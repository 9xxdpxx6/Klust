<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApplicationStatus;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Только админ может удалять пользователей
        return $user->hasRole('admin');
    }

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

        if (! $viewer->hasRole('partner')) {
            return false;
        }

        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');

        // Check if student is in any accepted team for partner's cases
        // As leader
        $asLeader = $student->caseApplications()
            ->where('status_id', $acceptedStatusId)
            ->whereHas('case', function ($q) use ($viewer) {
                $q->where('user_id', $viewer->id);
            })
            ->exists();

        // As team member
        $asMember = $student->caseTeamMembers()
            ->whereHas('application', function ($q) use ($acceptedStatusId, $viewer) {
                $q->where('status_id', $acceptedStatusId)
                    ->whereHas('case', function ($caseQ) use ($viewer) {
                        $caseQ->where('user_id', $viewer->id);
                    });
            })
            ->exists();

        return $asLeader || $asMember;
    }
}
