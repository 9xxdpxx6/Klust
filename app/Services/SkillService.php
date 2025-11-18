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
            ->withPivot(['level', 'points'])
            ->orderByDesc('pivot_level')
            ->orderByDesc('pivot_points')
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'category' => $skill->category,
                    'max_level' => $skill->max_level,
                    'level' => $skill->pivot->level,
                    'points' => $skill->pivot->points,
                    'progress_to_next_level' => $this->calculateProgressToNextLevel($skill->pivot->points, $skill->pivot->level),
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
    private function calculateProgressToNextLevel(int $points, int $currentLevel): array
    {
        // Example level thresholds (can be adjusted)
        $levelThresholds = [
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

        $nextLevel = $currentLevel + 1;

        if ($nextLevel > 10) {
            return [
                'percentage' => 100,
                'points_needed' => 0,
                'next_level' => null,
            ];
        }

        $currentLevelThreshold = $levelThresholds[$currentLevel];
        $nextLevelThreshold = $levelThresholds[$nextLevel];
        $pointsInCurrentLevel = $points - $currentLevelThreshold;
        $pointsNeededForNextLevel = $nextLevelThreshold - $currentLevelThreshold;

        return [
            'percentage' => round(($pointsInCurrentLevel / $pointsNeededForNextLevel) * 100, 2),
            'points_needed' => $nextLevelThreshold - $points,
            'next_level' => $nextLevel,
        ];
    }
}
