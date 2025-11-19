<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Collection;

class SearchService
{
    private const DEFAULT_LIMIT = 10;

    /**
     * Search cases by title and description
     */
    public function searchCases(string $query, int $limit = self::DEFAULT_LIMIT): Collection
    {
        return CaseModel::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->with(['partner', 'required_skills'])
            ->limit($limit)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id,
                    'title' => $case->title,
                    'description' => str_limit(strip_tags($case->description), 100),
                    'partner' => $case->partner->company_name ?? null,
                    'deadline' => $case->deadline?->format('d.m.Y'),
                    'type' => 'case',
                    'url' => route('student.cases.show', $case->id),
                ];
            });
    }

    /**
     * Search users by name and email
     */
    public function searchUsers(string $query, int $limit = self::DEFAULT_LIMIT): Collection
    {
        return User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => 'user',
                    'url' => '#',
                ];
            });
    }

    /**
     * Search skills by name
     */
    public function searchSkills(string $query, int $limit = self::DEFAULT_LIMIT): Collection
    {
        return Skill::where('name', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'description' => $skill->description,
                    'type' => 'skill',
                    'url' => '#',
                ];
            });
    }

    /**
     * Search across all entities
     */
    public function searchAll(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        return [
            'cases' => $this->searchCases($query, $limit),
            'users' => $this->searchUsers($query, $limit),
            'skills' => $this->searchSkills($query, $limit),
        ];
    }
}
