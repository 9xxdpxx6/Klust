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
     * Calculate progress to next level
     */
    private function calculateProgressToNextLevel(int $points, int $currentLevel, int $maxLevel): array
    {
        // Level thresholds - более плавная прогрессия
        // Используем полиномиальный рост вместо экспоненциального
        $levelThresholds = [
            1 => 0,
            2 => 50,
            3 => 120,
            4 => 210,
            5 => 320,
            6 => 450,
            7 => 600,
            8 => 770,
            9 => 960,
            10 => 1170,
        ];

        // Calculate threshold for levels above 10 using polynomial formula
        // Формула: base + (level - 10) * multiplier, где multiplier постепенно увеличивается
        for ($level = 11; $level <= $maxLevel; $level++) {
            if (!isset($levelThresholds[$level])) {
                // Более плавный рост: базовое значение + квадратичный рост
                // Для уровней 11-20: базовое 1170 + (level - 10) * 200 + (level - 10)^2 * 10
                // Для уровней 21+: более медленный рост
                $levelDiff = $level - 10;
                if ($level <= 20) {
                    $levelThresholds[$level] = 1170 + ($levelDiff * 200) + ($levelDiff * $levelDiff * 10);
                } elseif ($level <= 50) {
                    // Для средних уровней: более медленный рост
                    $base20 = $levelThresholds[20] ?? 1170 + (10 * 200) + (10 * 10 * 10);
                    $levelDiff20 = $level - 20;
                    $levelThresholds[$level] = $base20 + ($levelDiff20 * 500) + ($levelDiff20 * $levelDiff20 * 5);
                } else {
                    // Для высоких уровней: еще более медленный рост
                    $base50 = $levelThresholds[50] ?? 0;
                    if ($base50 === 0) {
                        // Вычисляем базовое значение для уровня 50
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $base50 = $base20 + (30 * 500) + (30 * 30 * 5);
                    }
                    $levelDiff50 = $level - 50;
                    $levelThresholds[$level] = $base50 + ($levelDiff50 * 1000) + ($levelDiff50 * $levelDiff50 * 2);
                }
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
        $currentLevelThreshold = $levelThresholds[$currentLevel] ?? ($levelThresholds[10] * pow(2, max(0, $currentLevel - 10)));
        $nextLevelThreshold = $levelThresholds[$nextLevel] ?? ($levelThresholds[10] * pow(2, max(0, $nextLevel - 10)));
        
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
