<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\CaseApplicationFilter;
use App\Models\ApplicationStatus;
use App\Models\CaseApplication;
use App\Models\CaseApplicationStatusHistory;
use App\Models\CaseModel;
use App\Models\CaseTeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicationService
{
    /**
     * Create case application
     */
    public function createApplication(User $leader, CaseModel $case, array $data): CaseApplication
    {
        $this->ensureCaseDeadlineNotPassed($case, 'создавать заявки');

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
            if (isset($data['team_members']) && is_array($data['team_members']) && count($data['team_members']) > 0) {
                $this->validateTeamMembers($data['team_members'], $case);
                $this->validateTeamMembersEligibility($data['team_members'], $case);

                foreach ($data['team_members'] as $userId) {
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

            return $application;
        });
    }

    /**
     * Approve application
     */
    public function approveApplication(CaseApplication $application, ?string $comment = null): CaseApplication
    {
        $application->loadMissing('case');
        $this->ensureCaseDeadlineNotPassed($application->case, 'изменять статус заявок');

        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Только заявки со статусом "ожидает" могут быть одобрены');
        }

        $oldStatusId = $application->status_id;

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

        return $application->fresh(['status']);
    }

    /**
     * Reject application
     */
    public function rejectApplication(CaseApplication $application, string $rejectionReason): CaseApplication
    {
        $application->loadMissing('case');
        $this->ensureCaseDeadlineNotPassed($application->case, 'изменять статус заявок');

        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $rejectedStatusId = ApplicationStatus::getIdByName('rejected');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Только заявки со статусом "ожидает" могут быть отклонены');
        }

        return DB::transaction(function () use ($application, $rejectionReason, $pendingStatusId, $rejectedStatusId) {
            $oldStatusId = $application->status_id;

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

            return $application->fresh(['status']);
        });
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus(CaseApplication $application, string $statusName, ?string $comment = null): CaseApplication
    {
        $application->loadMissing('case');
        $this->ensureCaseDeadlineNotPassed($application->case, 'изменять статус заявок');

        $newStatusId = ApplicationStatus::getIdByName($statusName);
        
        if (!$newStatusId) {
            throw new \Exception("Неизвестный статус: {$statusName}");
        }

        // Если статус не изменился, ничего не делаем
        if ($application->status_id === $newStatusId) {
            return $application;
        }

        return DB::transaction(function () use ($application, $statusName, $comment, $newStatusId) {
            $oldStatusId = $application->status_id;

            // Обновляем статус
            $updateData = [
                'status_id' => $newStatusId,
            ];

            // Если меняем на rejected, добавляем причину
            if ($statusName === 'rejected' && $comment) {
                $updateData['rejection_reason'] = $comment;
            }

            // Если меняем на accepted, очищаем rejection_reason
            if ($statusName === 'accepted') {
                $updateData['rejection_reason'] = null;
            }

            // Устанавливаем reviewed_at при изменении статуса с pending
            $pendingStatusId = ApplicationStatus::getIdByName('pending');
            if ($oldStatusId === $pendingStatusId) {
                $updateData['reviewed_at'] = now();
            }

            $application->update($updateData);

            // Записываем историю изменения статуса
            $this->recordStatusChange(
                $application,
                $oldStatusId,
                $newStatusId,
                Auth::id() ?? $application->case->partner->user_id,
                $comment ?? "Статус изменен на: {$statusName}"
            );

            return $application->fresh(['status']);
        });
    }

    /**
     * Withdraw application
     */
    public function withdrawApplication(CaseApplication $application): bool
    {
        $application->loadMissing('case');
        $this->ensureCaseDeadlineNotPassed($application->case, 'изменять статус заявок');

        $pendingStatusId = ApplicationStatus::getIdByName('pending');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Только заявки со статусом "ожидает" могут быть отозваны');
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
        $application->loadMissing('case');
        $this->ensureCaseDeadlineNotPassed($application->case, 'изменять состав команды');

        $pendingStatusId = ApplicationStatus::getIdByName('pending');

        if ($application->status_id !== $pendingStatusId) {
            throw new \Exception('Участников команды можно добавлять только к заявкам со статусом "ожидает"');
        }

        // Validate user is a student
        $user = User::findOrFail($userId);
        if (! $user->hasRole('student')) {
            throw new \Exception('Участник команды должен быть студентом');
        }

        // Validate team size
        $currentSize = $application->teamMembers()->count() + 1; // +1 for leader
        $requiredSize = $application->case->required_team_size;

        if ($currentSize >= $requiredSize) {
            throw new \Exception("Команда уже заполнена (максимум: {$requiredSize} человек)");
        }

        // Check if student is not in another team for this case
        $this->validateTeamMembersEligibility([$userId], $application->case);

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
        if ($user->caseApplications()->where('case_id', $case->id)->exists()) {
            return true;
        }

        // Check as team member
        $applicationIds = $case->applications()->pluck('id');

        return CaseTeamMember::where('user_id', $user->id)
            ->whereIn('application_id', $applicationIds)
            ->exists();
    }

    private function ensureCaseDeadlineNotPassed(CaseModel $case, string $action): void
    {
        if ($case->deadline && $case->deadline->isPast()) {
            throw new \Exception("Нельзя {$action} после дедлайна кейса.");
        }
    }

    /**
     * Get student's application status for case
     */
    public function getStudentApplicationStatus(User $user, CaseModel $case): ?CaseApplication
    {
        // First check as leader (более быстрый запрос)
        $application = CaseApplication::where('leader_id', $user->id)
            ->where('case_id', $case->id)
            ->first();

        if ($application) {
            return $application;
        }

        // Then check as team member (оптимизированный запрос)
        $teamMember = CaseTeamMember::where('user_id', $user->id)
            ->whereHas('application', function ($query) use ($case) {
                $query->where('case_id', $case->id);
            })
            ->with('application')
            ->first();

        return $teamMember?->application;
    }

    /**
     * Get student's cases grouped by status with pagination
     */
    public function getStudentCasesGrouped(User $user, Request $request): array
    {
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');
        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $rejectedStatusId = ApplicationStatus::getIdByName('rejected');

        // Base query: applications where user is leader OR team member
        $baseQuery = function () use ($user) {
            return CaseApplication::query()
                ->where(function ($query) use ($user) {
                    // User is leader
                    $query->where('leader_id', $user->id)
                        // OR user is team member
                        ->orWhereHas('teamMembers', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->whereHas('case') // Filter out applications with deleted cases
                ->with([
                    'case.partner',
                    'case.partnerUser.partnerProfile',
                    'teamMembers.user',
                    'status',
                    'leader'
                ]);
        };

        // Helper to get paginated applications for a status
        $getPaginatedApplications = function (int $statusId, string $tabKey) use ($baseQuery, $request) {
            $query = $baseQuery();
            $query->where('status_id', $statusId);

            // Get pagination parameters for this tab
            $pageKey = "page_{$tabKey}";
            $perPageKey = "per_page_{$tabKey}";
            
            $filters = [
                'page' => $request->query($pageKey, 1),
                'per_page' => $request->query($perPageKey, 12),
                'search' => $request->query('search'),
                'sort_by' => $request->query('sort_by', 'created_at'),
                'sort_order' => $request->query('sort_order', 'desc'),
            ];

            $filter = new CaseApplicationFilter($filters);
            $query = $filter->apply($query);
            
            $pagination = $filter->getPaginationParams();
            
            // Build query string for pagination links
            $queryParams = $request->query();
            $queryParams[$pageKey] = null; // Will be set by paginator
            
            return $query
                ->paginate($pagination['per_page'], ['*'], $pageKey)
                ->appends($queryParams);
        };

        return [
            'current' => $getPaginatedApplications($acceptedStatusId, 'current'),
            'pending' => $getPaginatedApplications($pendingStatusId, 'pending'),
            'completed' => $this->getEmptyPaginator($request, 'completed'),
            'rejected' => $getPaginatedApplications($rejectedStatusId, 'rejected'),
        ];
    }

    /**
     * Get empty paginator for completed status (not implemented yet)
     */
    private function getEmptyPaginator(Request $request, string $tabKey): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $pageKey = "page_{$tabKey}";
        $perPageKey = "per_page_{$tabKey}";
        
        $page = (int) $request->query($pageKey, 1);
        $perPage = (int) $request->query($perPageKey, 12);
        
        // Create empty collection paginator
        $items = collect();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            0,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageKey,
            ]
        );
    }

    /**
     * Validate team members are students
     */
    private function validateTeamMembers(array $userIds, CaseModel $case): void
    {
        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            if (! $user->hasRole('student')) {
                throw new \Exception("Пользователь {$user->name} не является студентом");
            }
        }
    }

    /**
     * Validate team member eligibility for case (batch validation for all members)
     */
    private function validateTeamMembersEligibility(array $userIds, CaseModel $case): void
    {
        if (empty($userIds)) {
            return;
        }

        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');

        // Get existing application IDs for this case in one query
        $existingApplicationIds = $case->applications()
            ->whereIn('status_id', [$pendingStatusId, $acceptedStatusId])
            ->pluck('id')
            ->toArray();

        if (empty($existingApplicationIds)) {
            // No existing applications, check only if users are leaders
            $existingLeaders = CaseApplication::where('case_id', $case->id)
                ->whereIn('leader_id', $userIds)
                ->whereIn('status_id', [$pendingStatusId, $acceptedStatusId])
                ->pluck('leader_id')
                ->toArray();

            if (!empty($existingLeaders)) {
                $user = User::whereIn('id', $existingLeaders)->first();
                throw new \Exception("Студент {$user->name} уже является лидером команды для этого кейса");
            }
            return;
        }

        // Check if any user is already in another team (batch query)
        $usersInTeams = CaseTeamMember::whereIn('user_id', $userIds)
            ->whereIn('application_id', $existingApplicationIds)
            ->with('user:id,name')
            ->get();

        if ($usersInTeams->isNotEmpty()) {
            $user = $usersInTeams->first()->user;
            throw new \Exception("Студент {$user->name} уже участвует в другой команде для этого кейса");
        }

        // Check if any user is leader of another application (batch query)
        $existingLeaders = CaseApplication::where('case_id', $case->id)
            ->whereIn('leader_id', $userIds)
            ->whereIn('status_id', [$pendingStatusId, $acceptedStatusId])
            ->with('leader:id,name')
            ->get();

        if ($existingLeaders->isNotEmpty()) {
            $user = $existingLeaders->first()->leader;
            throw new \Exception("Студент {$user->name} уже является лидером команды для этого кейса");
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
                "Размер команды ({$teamSize}) превышает требуемый размер ({$case->required_team_size})"
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
