<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApplicationStatus;
use App\Models\CaseApplication;
use App\Models\CaseApplicationStatusHistory;
use App\Models\CaseModel;
use App\Models\CaseTeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    /**
     * Create case application
     */
    public function createApplication(User $leader, CaseModel $case, array $data): CaseApplication
    {
        return DB::transaction(function () use ($leader, $case, $data) {
            // Create application
            $application = CaseApplication::create([
                'case_id' => $case->id,
                'leader_id' => $leader->id,
                'motivation' => $data['motivation'] ?? null,
                'status_id' => ApplicationStatus::getIdByName('pending'),
                'submitted_at' => now(),
            ]);

            // Add team members if provided
            if (isset($data['team_members']) && is_array($data['team_members'])) {
                $this->validateTeamMembers($data['team_members'], $case);

                foreach ($data['team_members'] as $userId) {
                    $this->validateTeamMemberEligibility($userId, $case);

                    CaseTeamMember::create([
                        'application_id' => $application->id,
                        'user_id' => $userId,
                    ]);
                }
            }

            // Validate team size
            $this->validateTeamSize($application, $case);

            // Record status history
            $this->recordStatusChange(
                $application,
                null,
                ApplicationStatus::getIdByName('pending'),
                $leader->id,
                'Заявка подана'
            );

            return $application->fresh(['teamMembers', 'leader', 'case']);
        });
    }

    /**
     * Approve application
     */
    public function approveApplication(CaseApplication $application, ?string $comment = null): CaseApplication
    {
        $pendingStatusId = ApplicationStatus::getIdByName('pending');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Only pending applications can be approved');
        }

        $oldStatusId = $application->status_id;
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');

        $application->update([
            'status_id' => $acceptedStatusId,
            'partner_comment' => $comment,
            'reviewed_at' => now(),
        ]);

        // Record status history
        $this->recordStatusChange(
            $application,
            $oldStatusId,
            $acceptedStatusId,
            Auth::id() ?? $application->case->partner->user_id,
            $comment ?? 'Заявка одобрена партнером'
        );

        return $application->fresh();
    }

    /**
     * Reject application
     */
    public function rejectApplication(CaseApplication $application, string $rejectionReason): CaseApplication
    {
        $pendingStatusId = ApplicationStatus::getIdByName('pending');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Only pending applications can be rejected');
        }

        $oldStatusId = $application->status_id;
        $rejectedStatusId = ApplicationStatus::getIdByName('rejected');

        $application->update([
            'status_id' => $rejectedStatusId,
            'rejection_reason' => $rejectionReason,
            'reviewed_at' => now(),
        ]);

        // Record status history
        $this->recordStatusChange(
            $application,
            $oldStatusId,
            $rejectedStatusId,
            Auth::id() ?? $application->case->partner->user_id,
            $rejectionReason
        );

        return $application->fresh();
    }

    /**
     * Withdraw application
     */
    public function withdrawApplication(CaseApplication $application): bool
    {
        $pendingStatusId = ApplicationStatus::getIdByName('pending');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Only pending applications can be withdrawn');
        }

        return DB::transaction(function () use ($application) {
            // Delete team members first
            $application->teamMembers()->delete();

            // Delete application
            return $application->delete();
        });
    }

    /**
     * Add team member to application
     */
    public function addTeamMember(CaseApplication $application, int $userId): CaseTeamMember
    {
        $pendingStatusId = ApplicationStatus::getIdByName('pending');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Can only add team members to pending applications');
        }

        // Validate user is a student
        $user = User::findOrFail($userId);
        if (! $user->hasRole('student')) {
            throw new \Exception('Team member must be a student');
        }

        // Validate team size
        $currentSize = $application->teamMembers()->count() + 1; // +1 for leader
        $requiredSize = $application->case->required_team_size;

        if ($currentSize >= $requiredSize) {
            throw new \Exception("Team is already full (max: {$requiredSize})");
        }

        // Check if student is not in another team for this case
        $this->validateTeamMemberEligibility($userId, $application->case);

        return CaseTeamMember::create([
            'application_id' => $application->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Check if user has application for case
     */
    public function hasApplication(User $user, CaseModel $case): bool
    {
        // Check as leader
        if ($user->caseApplicationsAsLeader()->where('case_id', $case->id)->exists()) {
            return true;
        }

        // Check as team member
        $applicationIds = $case->applications()->pluck('id');

        return CaseTeamMember::where('user_id', $user->id)
            ->whereIn('application_id', $applicationIds)
            ->exists();
    }

    /**
     * Get student's application status for case
     */
    public function getStudentApplicationStatus(User $user, CaseModel $case): ?CaseApplication
    {
        // First check as leader
        $application = $user->caseApplicationsAsLeader()
            ->where('case_id', $case->id)
            ->first();

        if ($application) {
            return $application;
        }

        // Then check as team member
        $applicationIds = $case->applications()->pluck('id');

        $teamMember = CaseTeamMember::where('user_id', $user->id)
            ->whereIn('application_id', $applicationIds)
            ->with('application')
            ->first();

        return $teamMember?->application;
    }

    /**
     * Get student's cases grouped by status
     */
    public function getStudentCasesGrouped(User $user): array
    {
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');
        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $rejectedStatusId = ApplicationStatus::getIdByName('rejected');

        // Get applications where user is leader
        $leaderApplications = $user->caseApplicationsAsLeader()
            ->with(['case.partner', 'teamMembers.user', 'status'])
            ->get();

        // Get applications where user is team member
        $teamMemberApplicationIds = CaseTeamMember::where('user_id', $user->id)
            ->pluck('application_id');

        $teamMemberApplications = CaseApplication::whereIn('id', $teamMemberApplicationIds)
            ->with(['case.partner', 'leader', 'teamMembers.user', 'status'])
            ->get();

        // Merge and group
        $allApplications = $leaderApplications->merge($teamMemberApplications)->unique('id');

        return [
            'current' => $allApplications->where('status_id', $acceptedStatusId)->values(),
            'pending' => $allApplications->where('status_id', $pendingStatusId)->values(),
            'completed' => collect(), // No completed status yet
            'rejected' => $allApplications->where('status_id', $rejectedStatusId)->values(),
        ];
    }

    /**
     * Validate team members are students
     */
    private function validateTeamMembers(array $userIds, CaseModel $case): void
    {
        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            if (! $user->hasRole('student')) {
                throw new \Exception("User {$user->name} is not a student");
            }
        }
    }

    /**
     * Validate team member eligibility for case
     */
    private function validateTeamMemberEligibility(int $userId, CaseModel $case): void
    {
        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');

        // Check if user is already in another team for this case
        $existingApplicationIds = $case->applications()
            ->whereIn('status_id', [$pendingStatusId, $acceptedStatusId])
            ->pluck('id');

        $isAlreadyInTeam = CaseTeamMember::where('user_id', $userId)
            ->whereIn('application_id', $existingApplicationIds)
            ->exists();

        if ($isAlreadyInTeam) {
            throw new \Exception('Student is already in another team for this case');
        }

        // Check if user is leader of another application
        $isLeader = CaseApplication::where('leader_id', $userId)
            ->where('case_id', $case->id)
            ->whereIn('status_id', [$pendingStatusId, $acceptedStatusId])
            ->exists();

        if ($isLeader) {
            throw new \Exception('Student is already a leader for this case');
        }
    }

    /**
     * Validate team size
     */
    private function validateTeamSize(CaseApplication $application, CaseModel $case): void
    {
        $teamSize = $application->teamMembers()->count() + 1; // +1 for leader

        if ($teamSize > $case->required_team_size) {
            throw new \Exception(
                "Team size ({$teamSize}) exceeds required size ({$case->required_team_size})"
            );
        }
    }

    /**
     * Record status change in history
     */
    private function recordStatusChange(
        CaseApplication $application,
        ?int $oldStatusId,
        int $newStatusId,
        int $changedBy,
        ?string $comment = null
    ): void {
        CaseApplicationStatusHistory::create([
            'case_application_id' => $application->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'comment' => $comment,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }
}
