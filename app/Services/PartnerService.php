<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseApplication;
use App\Models\User;

class PartnerService
{
    /**
     * Get dashboard statistics for partner
     */
    public function getDashboardStatistics(User $user): array
    {
        if (! $user->hasRole('partner')) {
            return [
                'total_cases' => 0,
                'active_cases' => 0,
                'completed_cases' => 0,
                'draft_cases' => 0,
                'archived_cases' => 0,
                'total_teams' => 0,
                'pending_applications' => 0,
            ];
        }

        $cases = \App\Models\CaseModel::where('user_id', $user->id);

        return [
            'total_cases' => $cases->count(),
            'active_cases' => (clone $cases)->where('status', 'active')->count(),
            'completed_cases' => (clone $cases)->where('status', 'completed')->count(),
            'draft_cases' => (clone $cases)->where('status', 'draft')->count(),
            'archived_cases' => (clone $cases)->where('status', 'archived')->count(),
            'total_teams' => (clone $cases)
                ->withCount(['applications' => function ($q) {
                    $q->accepted();
                }])
                ->get()
                ->sum('applications_count'),
            'pending_applications' => (clone $cases)
                ->withCount(['applications' => function ($q) {
                    $q->pending();
                }])
                ->get()
                ->sum('applications_count'),
        ];
    }

    /**
     * Get recent activities for partner dashboard
     * Returns new applications, completed cases, and new teams
     */
    public function getRecentActivities(User $user): array
    {
        if (! $user->hasRole('partner')) {
            return [
                'newApplications' => [],
                'completedCases' => [],
                'newTeams' => [],
            ];
        }

        // Новые заявки (pending applications) за последние 7 дней
        $newApplications = CaseApplication::query()
            ->whereHas('case', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->pending()
            ->with(['case', 'leader', 'teamMembers.user'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'case_id' => $application->case_id,
                    'case' => $application->case ? [
                        'id' => $application->case->id,
                        'title' => $application->case->title,
                    ] : null,
                    'leader' => $application->leader ? [
                        'id' => $application->leader->id,
                        'name' => $application->leader->name,
                    ] : null,
                    'team_members' => $application->teamMembers->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'user' => $member->user ? [
                                'id' => $member->user->id,
                                'name' => $member->user->name,
                            ] : null,
                        ];
                    })->toArray(),
                    'members_count' => $application->teamMembers->count() + 1, // +1 для лидера
                    'created_at' => $application->created_at,
                ];
            });

        // Завершенные кейсы (все, отсортированные по дате завершения)
        $completedCases = \App\Models\CaseModel::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id,
                    'title' => $case->title,
                    'completed_at' => $case->updated_at,
                    'updated_at' => $case->updated_at,
                ];
            });

        // Новые команды (accepted applications) за последние 7 дней
        $newTeams = CaseApplication::query()
            ->whereHas('case', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->accepted()
            ->with(['case', 'leader', 'teamMembers.user'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($application) {
                $teamMembers = collect([$application->leader])
                    ->merge($application->teamMembers->pluck('user'))
                    ->filter()
                    ->map(fn ($member) => $member->name ?? 'Неизвестно');

                return [
                    'id' => $application->id,
                    'case_id' => $application->case_id,
                    'case' => $application->case ? [
                        'id' => $application->case->id,
                        'title' => $application->case->title,
                    ] : null,
                    'leader' => $application->leader ? [
                        'id' => $application->leader->id,
                        'name' => $application->leader->name,
                    ] : null,
                    'team_members' => $application->teamMembers->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'user' => $member->user ? [
                                'id' => $member->user->id,
                                'name' => $member->user->name,
                            ] : null,
                        ];
                    })->toArray(),
                    'members_count' => $application->teamMembers->count() + 1, // +1 для лидера
                    'created_at' => $application->created_at,
                ];
            });

        return [
            'newApplications' => $newApplications->values()->toArray(),
            'completedCases' => $completedCases->values()->toArray(),
            'newTeams' => $newTeams->values()->toArray(),
        ];
    }
}
