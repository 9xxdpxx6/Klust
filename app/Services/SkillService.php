<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Collection;

class SkillService
{
    /**
     * Create new skill
     */
    public function createSkill(array $data): Skill
    {
        return Skill::create([
            'name' => $data['name'],
            'category' => $data['category'],
            'max_level' => $data['max_level'],
        ]);
    }

    /**
     * Update skill
     */
    public function updateSkill(Skill $skill, array $data): Skill
    {
        $skill->update([
            'name' => $data['name'] ?? $skill->name,
            'category' => $data['category'] ?? $skill->category,
            'max_level' => $data['max_level'] ?? $skill->max_level,
        ]);

        return $skill->fresh();
    }

    /**
     * Delete skill
     */
    public function deleteSkill(Skill $skill): bool
    {
        // Check if skill is used in cases
        $usedInCases = $skill->cases()->count();
        if ($usedInCases > 0) {
            throw new \Exception("Cannot delete skill that is used in {$usedInCases} case(s)");
        }

        // Check if skill is assigned to users
        $usedByUsers = $skill->users()->count();
        if ($usedByUsers > 0) {
            throw new \Exception("Cannot delete skill that is assigned to {$usedByUsers} user(s)");
        }

        return $skill->delete();
    }

    /**
     * Get student skills with levels and points
     */
    public function getStudentSkills(User $user): Collection
    {
        return $user->skills()
            ->withPivot(['level', 'points_earned'])
            ->orderByDesc('pivot_level')
            ->orderByDesc('pivot_points_earned')
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'category' => $skill->category,
                    'max_level' => $skill->max_level,
                    'level' => $skill->pivot->level,
                    'points' => $skill->pivot->points_earned,
                    'progress_to_next_level' => $this->calculateProgressToNextLevel(
                        $skill->pivot->points_earned,
                        $skill->pivot->level,
                        $skill->max_level
                    ),
                ];
            });
    }

    /**
     * Get filtered skills with pagination
     */
    public function getFilteredSkills(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $skillFilter = new \App\Filters\SkillFilter($filters);

        $query = Skill::query()
            ->withCount(['users', 'cases']);

        $query = $skillFilter->apply($query);

        $pagination = $skillFilter->getPaginationParams();

        return $query->paginate($pagination['per_page']);
    }

    /**
     * Get maximum level in the system (from all skills)
     */
    public function getMaxLevelInSystem(): int
    {
        return Skill::max('max_level') ?? 10;
    }

    /**
     * Calculate progress to next level using shared config thresholds.
     */
    private function calculateProgressToNextLevel(int $points, int $currentLevel, int $maxLevel): array
    {
        // Use the same thresholds as ProgressLogService and StudentService
        $levelThresholds = config('skills.level_thresholds');

        // Extend thresholds for levels above the config max using exponential growth
        $configMaxLevel = max(array_keys($levelThresholds));
        $configMaxValue = $levelThresholds[$configMaxLevel];
        $prevThreshold = $configMaxValue;
        for ($level = $configMaxLevel + 1; $level <= $maxLevel; $level++) {
            if (! isset($levelThresholds[$level])) {
                $nextThreshold = $prevThreshold * 2;
                // Overflow protection: cap at a safe maximum when multiplication overflows
                if ($nextThreshold <= $prevThreshold || $nextThreshold > PHP_INT_MAX / 2) {
                    $nextThreshold = $prevThreshold + $configMaxValue;
                }
                $levelThresholds[$level] = $nextThreshold;
                $prevThreshold = $nextThreshold;
            } else {
                $prevThreshold = $levelThresholds[$level];
            }
        }

        // If user reached max level for this skill, show 100% progress
        if ($currentLevel >= $maxLevel) {
            return [
                'percentage' => 100,
                'points_needed' => 0,
                'next_level' => null,
            ];
        }

        $nextLevel = $currentLevel + 1;

        // Check if next level would exceed max level
        if ($nextLevel > $maxLevel) {
            return [
                'percentage' => 100,
                'points_needed' => 0,
                'next_level' => null,
            ];
        }

        // Get thresholds for current and next level
        $currentLevelThreshold = $levelThresholds[$currentLevel] ?? 0;
        $nextLevelThreshold = $levelThresholds[$nextLevel] ?? ($currentLevelThreshold * 2);

        $pointsInCurrentLevel = $points - $currentLevelThreshold;
        $pointsNeededForNextLevel = $nextLevelThreshold - $currentLevelThreshold;

        // Avoid division by zero
        if ($pointsNeededForNextLevel <= 0) {
            return [
                'percentage' => 100,
                'points_needed' => 0,
                'next_level' => null,
            ];
        }

        // Ensure percentage doesn't exceed 100%
        $percentage = min(100, round(($pointsInCurrentLevel / $pointsNeededForNextLevel) * 100, 2));

        return [
            'percentage' => $percentage,
            'points_needed' => max(0, $nextLevelThreshold - $points),
            'next_level' => $nextLevel,
        ];
    }
}
