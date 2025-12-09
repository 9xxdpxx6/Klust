<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApplicationStatus;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view student profile (for partners).
     * Partner can view profile only if student is in accepted team of partner's case.
     */
    public function viewStudentProfile(User $viewer, User $student): bool
    {
        // Viewer must be a partner
        if (! $viewer->hasRole('partner')) {
            return false;
        }

        // Student must have student role
        if (! $student->hasRole('student')) {
            return false;
        }

        $partner = $viewer->partner;
        if (! $partner) {
            return false;
        }

        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');

        // Check if student is in any accepted team for partner's cases
        // As leader
        $asLeader = $student->caseApplications()
            ->where('status_id', $acceptedStatusId)
            ->whereHas('case', function ($q) use ($partner) {
                $q->where('partner_id', $partner->id);
            })
            ->exists();

        // As team member
        $asMember = $student->caseTeamMembers()
            ->whereHas('application', function ($q) use ($acceptedStatusId, $partner) {
                $q->where('status_id', $acceptedStatusId)
                    ->whereHas('case', function ($caseQ) use ($partner) {
                        $caseQ->where('partner_id', $partner->id);
                    });
            })
            ->exists();

        return $asLeader || $asMember;
    }
}
