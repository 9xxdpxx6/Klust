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
     * Log simulator completion and award points
     */
    public function logSimulatorCompletion(SimulatorSession $session): void
    {
        DB::transaction(function () use ($session) {
            $user = $session->user;
            $simulator = $session->simulator;

            // Calculate points based on score
            $pointsEarned = $this->calculatePointsFromScore($session->score);

            // Check if simulator is linked to a case with required skills
            $case = $simulator->cases()->first();

            if ($case && $case->skills->isNotEmpty()) {
                // Award points to case-related skills
                foreach ($case->skills as $skill) {
                    $this->awardSkillPoints($user, $skill, $pointsEarned, 'simulator_completion', [
                        'simulator_id' => $simulator->id,
                        'session_id' => $session->id,
                        'score' => $session->score,
                    ]);
                }
            } else {
                // Award points to general skills or simulator-specific skills
                // For now, we'll skip if no skills are associated
                // This can be extended to have default skills per simulator
            }

            // Update student total points
            $studentProfile = $user->studentProfile;
            if ($studentProfile) {
                $studentProfile->increment('total_points', $pointsEarned);
            }

            // Check for new badges
            $this->checkAndAwardBadges($user);
        });
    }

    /**
     * Award skill points to user
     */
    public function awardSkillPoints(
        User $user,
        Skill $skill,
        int $points,
        string $source = 'manual',
        array $metadata = []
    ): void {
        DB::transaction(function () use ($user, $skill, $points, $source, $metadata) {
            // Get or create user skill
            $userSkill = UserSkill::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'skill_id' => $skill->id,
                ],
                [
                    'level' => 1,
                    'points' => 0,
                ]
            );

            $oldLevel = $userSkill->level;
            $oldPoints = $userSkill->points;

            // Add points
            $newPoints = $oldPoints + $points;
            $newLevel = $this->calculateLevelFromPoints($newPoints);

            $userSkill->update([
                'points' => $newPoints,
                'level' => $newLevel,
            ]);

            // Create progress log
            ProgressLog::create([
                'user_id' => $user->id,
                'skill_id' => $skill->id,
                'points_earned' => $points,
                'source' => $source,
                'description' => $this->generateProgressDescription($source, $skill, $points, $metadata),
            ]);

            // Notify if level up
            if ($newLevel > $oldLevel) {
                $this->notificationService->notifySkillLevelUp($user, $skill->name, $newLevel);
            }

            // Update student total points
            $studentProfile = $user->studentProfile;
            if ($studentProfile) {
                $studentProfile->increment('total_points', $points);
            }

            // Check for new badges
            $this->checkAndAwardBadges($user);
        });
    }

    /**
     * Get skill progress history for user
     */
    public function getSkillProgressHistory(User $user, ?Skill $skill = null): Collection
    {
        $query = ProgressLog::where('user_id', $user->id)
            ->with('skill');

        if ($skill) {
            $query->where('skill_id', $skill->id);
        }

        return $query->latest()->get();
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
     * Calculate level from points
     */
    private function calculateLevelFromPoints(int $points): int
    {
        // Level thresholds
        $thresholds = [
            1 => 0,
            2 => 100,
            3 => 250,
            4 => 500,
            5 => 1000,
            6 => 2000,
            7 => 4000,
            8 => 8000,
            9 => 16000,
            10 => 32000,
        ];

        $level = 1;
        foreach ($thresholds as $lvl => $threshold) {
            if ($points >= $threshold) {
                $level = $lvl;
            } else {
                break;
            }
        }

        return $level;
    }

    /**
     * Check and award new badges
     */
    private function checkAndAwardBadges(User $user): void
    {
        $newBadges = $this->badgeService->checkBadgeEligibility($user);

        foreach ($newBadges as $badge) {
            $this->notificationService->notifyBadgeEarned($user, $badge->name);
        }
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
