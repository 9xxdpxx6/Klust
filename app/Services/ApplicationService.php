<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\CaseTeamMember;
use App\Models\User;
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
                'status' => 'pending',
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

            return $application->fresh(['teamMembers', 'leader', 'case']);
        });
    }

    /**
     * Approve application
     */
    public function approveApplication(CaseApplication $application, ?string $comment = null): CaseApplication
    {
        if ($application->status !== 'pending') {
            throw new \Exception('Only pending applications can be approved');
        }

        $application->update([
            'status' => 'accepted',
            'partner_comment' => $comment,
            'reviewed_at' => now(),
        ]);

        return $application->fresh();
    }

    /**
     * Reject application
     */
    public function rejectApplication(CaseApplication $application, string $rejectionReason): CaseApplication
    {
        if ($application->status !== 'pending') {
            throw new \Exception('Only pending applications can be rejected');
        }

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
            'reviewed_at' => now(),
        ]);

        return $application->fresh();
    }

    /**
     * Withdraw application
     */
    public function withdrawApplication(CaseApplication $application): bool
    {
        if ($application->status !== 'pending') {
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
        if ($application->status !== 'pending') {
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
        // Get applications where user is leader
        $leaderApplications = $user->caseApplicationsAsLeader()
            ->with(['case.partner', 'teamMembers.user'])
            ->get();

        // Get applications where user is team member
        $teamMemberApplicationIds = CaseTeamMember::where('user_id', $user->id)
            ->pluck('application_id');

        $teamMemberApplications = CaseApplication::whereIn('id', $teamMemberApplicationIds)
            ->with(['case.partner', 'leader', 'teamMembers.user'])
            ->get();

        // Merge and group
        $allApplications = $leaderApplications->merge($teamMemberApplications)->unique('id');

        return [
            'current' => $allApplications->where('status', 'accepted')->values(),
            'pending' => $allApplications->where('status', 'pending')->values(),
            'completed' => $allApplications->where('status', 'completed')->values(),
            'rejected' => $allApplications->where('status', 'rejected')->values(),
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
        // Check if user is already in another team for this case
        $existingApplicationIds = $case->applications()
            ->whereIn('status', ['pending', 'accepted'])
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
            ->whereIn('status', ['pending', 'accepted'])
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
}
