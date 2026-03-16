<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProgressLog;
use App\Models\SimulatorSession;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProgressLogService
{
    public function __construct(
        private BadgeService $badgeService,
        private NotificationService $notificationService
    ) {}

    /**
     * Log simulator completion and award points.
     *
     * DB operations (points, badges) run inside a transaction.
     * Email notifications are deferred to AFTER the HTTP response
     * so they never block the request or cause timeouts.
     */
    public function logSimulatorCompletion(SimulatorSession $session): void
    {
        $newBadges = [];
        $user = null;

        DB::transaction(function () use ($session, &$newBadges, &$user) {
            // Lock session row to make completion logging idempotent under concurrent requests.
            $lockedSession = SimulatorSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Points were already awarded by another request.
            // DB default is 0, so we check > 0 to distinguish from "not yet awarded".
            if ($lockedSession->points_earned > 0) {
                return;
            }

            $user = $lockedSession->user;
            $simulator = $lockedSession->simulator;

            // $session->score is already a final normalized 0-100 score
            // (aggregated from per-variant normalized_scores by the frontend).
            // Do NOT re-normalize with max_score — that would be double normalization.
            $finalScore = min(100, max(0, (int) $lockedSession->score));

            // Use the raw normalized score for points calculation.
            // The weighted_score from evaluation accounts for category weights and
            // can differ significantly from the displayed score, leading to incorrect awards.
            $scoreForPoints = $finalScore;

            // Calculate points based on score (0-100)
            $pointsEarned = $this->calculatePointsFromScore($scoreForPoints);

            // Collect skills from all sources:
            // 1) Skills linked directly to the simulator (simulator_skills pivot)
            // 2) Skills from cases linked to this simulator (case_skills pivot)
            $skills = collect();

            // Direct simulator → skills
            if ($simulator->skills->isNotEmpty()) {
                $skills = $skills->merge($simulator->skills);
            }

            // Case → skills (fallback / additional)
            $case = $simulator->cases()->first();
            if ($case && $case->skills->isNotEmpty()) {
                $skills = $skills->merge($case->skills);
            }

            // Deduplicate by skill ID
            $skills = $skills->unique('id');

            // Award points to each skill
            foreach ($skills as $skill) {
                $this->awardSkillPoints(
                    $user,
                    $skill,
                    $pointsEarned,
                    'simulator_completion',
                    [
                        'simulator_id' => $simulator->id,
                        'session_id' => $lockedSession->id,
                        'score' => $lockedSession->score,
                    ],
                    false // Don't update total_points per skill
                );
            }

            // Update student total points once
            $studentProfile = $user->studentProfile;
            if ($studentProfile) {
                $studentProfile->increment('total_points', $pointsEarned);
            }

            // Save points_earned on the session itself (for display in history)
            $lockedSession->update(['points_earned' => $pointsEarned]);

            // Check and award badges (DB operation)
            $newBadges = $this->badgeService->checkBadgeEligibility($user);

            // Send in-app (database) notifications for new badges
            foreach ($newBadges as $badgeData) {
                $this->notificationService->notifyBadgeEarned($user, $badgeData['badge']);
            }
        });
    }

    /**
     * Award skill points to user.
     * DB operations run in a transaction; notifications are deferred.
     */
    public function awardSkillPoints(
        User $user,
        Skill $skill,
        int $points,
        string $source = 'manual',
        array $metadata = [],
        bool $updateTotalPoints = true
    ): void {
        $leveledUp = false;
        $newLevel = 0;
        $newBadges = [];

        DB::transaction(function () use ($user, $skill, $points, $source, $metadata, $updateTotalPoints, &$leveledUp, &$newLevel, &$newBadges) {
            // Get or create user skill
            $userSkill = UserSkill::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'skill_id' => $skill->id,
                ],
                [
                    'level' => 1,
                    'points_earned' => 0,
                ]
            );

            $oldLevel = $userSkill->level;
            $oldPoints = $userSkill->points_earned;

            // Add points
            $newPoints = $oldPoints + $points;
            $newLevel = $this->calculateLevelFromPoints($newPoints);

            $userSkill->update([
                'points_earned' => $newPoints,
                'level' => $newLevel,
            ]);

            // Create progress log using polymorphic relationship
            ProgressLog::create([
                'user_id' => $user->id,
                'action' => $source,
                'loggable_type' => Skill::class,
                'loggable_id' => $skill->id,
                'points_earned' => $points,
            ]);

            // Track if level up occurred (notification sent later)
            if ($newLevel > $oldLevel) {
                $leveledUp = true;
            }

            // Update student total points only if requested
            if ($updateTotalPoints) {
                $studentProfile = $user->studentProfile;
                if ($studentProfile) {
                    $studentProfile->increment('total_points', $points);
                }

                // Check for new badges (DB only)
                $newBadges = $this->badgeService->checkBadgeEligibility($user);
            }
        });

        // Send in-app (database) notifications — instant, no SMTP
        if ($leveledUp) {
            $this->notificationService->notifySkillLevelUp($user, $skill->name, $newLevel);
        }

        if ($updateTotalPoints && !empty($newBadges)) {
            foreach ($newBadges as $badgeData) {
                $this->notificationService->notifyBadgeEarned($user, $badgeData['badge']);
            }
        }
    }

    /**
     * Get skill progress history for user
     */
    public function getSkillProgressHistory(User $user, ?Skill $skill = null): Collection
    {
        $query = ProgressLog::where('user_id', $user->id)
            ->forSkills()
            ->with('loggable');

        if ($skill) {
            $query->forSkill($skill);
        }

        $logs = $query->latest()->get();
        
        // Фильтруем записи, где loggable существует (навык не удален)
        return $logs->filter(function ($log) {
            return $log->loggable !== null;
        })->values();
    }

    /**
     * Calculate points from simulator score
     */
    private function calculatePointsFromScore(?int $score): int
    {
        if ($score === null) {
            return 10; // Default points
        }

        // Example: 1 point per score point, with bonus for high scores
        if ($score >= 90) {
            return $score + 20; // Bonus for excellent performance
        }

        if ($score >= 75) {
            return $score + 10; // Bonus for good performance
        }

        if ($score >= 50) {
            return $score; // Standard points
        }

        return max(10, (int) ($score * 0.5)); // Minimum 10 points
    }

    /**
     * Calculate level from points using shared config thresholds.
     * Extends beyond config max using exponential growth (same formula as SkillService).
     */
    private function calculateLevelFromPoints(int $points): int
    {
        $thresholds = config('skills.level_thresholds');

        $level = 1;
        foreach ($thresholds as $lvl => $threshold) {
            if ($points >= $threshold) {
                $level = $lvl;
            } else {
                break;
            }
        }

        // Extend beyond config max if points exceed the highest configured threshold
        $configMaxLevel = max(array_keys($thresholds));
        $configMaxValue = $thresholds[$configMaxLevel];

        if ($level === $configMaxLevel && $points >= $configMaxValue) {
            $nextThreshold = $configMaxValue;
            while (true) {
                $next = $nextThreshold * 2;
                // Overflow protection: stop if multiplication overflows or exceeds safe bounds
                if ($next <= $nextThreshold || $next > PHP_INT_MAX / 2) {
                    break;
                }
                if ($points < $next) {
                    break;
                }
                $level++;
                $nextThreshold = $next;
            }
        }

        return $level;
    }

    /**
     * Check and award new badges (DB only, no notifications).
     * Notifications are handled by deferBadgeNotifications().
     *
     * @return array Newly awarded badges for notification purposes
     */
    private function checkAndAwardBadges(User $user): array
    {
        return $this->badgeService->checkBadgeEligibility($user);
    }

    /**
     * Generate progress description
     */
    private function generateProgressDescription(
        string $source,
        Skill $skill,
        int $points,
        array $metadata
    ): string {
        return match ($source) {
            'simulator_completion' => "Earned {$points} points in {$skill->name} by completing simulator",
            'case_completion' => "Earned {$points} points in {$skill->name} by completing case",
            'manual' => "Manually awarded {$points} points in {$skill->name}",
            default => "Earned {$points} points in {$skill->name}",
        };
    }
}
