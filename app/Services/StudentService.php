<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseTeamMember;
use App\Models\User;

class StudentService
{
    /**
     * Get dashboard statistics for student
     */
    public function getDashboardStatistics(User $user): array
    {
        $studentProfile = $user->studentProfile;

        // Get active cases (as leader or team member)
        $activeAsLeader = $user->caseApplicationsAsLeader()
            ->accepted()
            ->count();

        $activeAsTeamMember = CaseTeamMember::where('user_id', $user->id)
            ->whereHas('application', function ($q) {
                $q->accepted();
            })
            ->count();

        // Get completed cases
        $completedAsLeader = $user->caseApplicationsAsLeader()
            ->completed()
            ->count();

        $completedAsTeamMember = CaseTeamMember::where('user_id', $user->id)
            ->whereHas('application', function ($q) {
                $q->completed();
            })
            ->count();

        // Get pending applications
        $pendingApplications = $user->caseApplicationsAsLeader()
            ->pending()
            ->count();

        return [
            'total_points' => $studentProfile?->total_points ?? 0,
            'active_cases' => $activeAsLeader + $activeAsTeamMember,
            'completed_cases' => $completedAsLeader + $completedAsTeamMember,
            'pending_applications' => $pendingApplications,
            'badges_count' => $user->badges()->count(),
            'skills_count' => $user->skills()->count(),
        ];
    }
}
