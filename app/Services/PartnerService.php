<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class PartnerService
{
    /**
     * Get dashboard statistics for partner
     */
    public function getDashboardStatistics(User $user): array
    {
        $partner = $user->partner;

        if (! $partner) {
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

        $cases = $partner->cases();

        return [
            'total_cases' => $cases->count(),
            'active_cases' => (clone $cases)->where('status', 'active')->count(),
            'completed_cases' => (clone $cases)->where('status', 'completed')->count(),
            'draft_cases' => (clone $cases)->where('status', 'draft')->count(),
            'archived_cases' => (clone $cases)->where('status', 'archived')->count(),
            'total_teams' => $partner->cases()
                ->withCount(['applications' => function ($q) {
                    $q->where('status', 'accepted');
                }])
                ->get()
                ->sum('applications_count'),
            'pending_applications' => $partner->cases()
                ->withCount(['applications' => function ($q) {
                    $q->where('status', 'pending');
                }])
                ->get()
                ->sum('applications_count'),
        ];
    }
}
