<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\CaseFilter;
use App\Helpers\FilterHelper;
use App\Jobs\UpdateCaseStatusByDeadline;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\CaseTeamMember;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CaseService
{
    /**
     * Get case IDs where user already has an application (as leader or team member)
     */
    public function getAppliedCaseIdsForUser(User $user): Collection
    {
        $leaderCaseIds = $user->caseApplications()->pluck('case_id');

        $teamMemberApplicationIds = CaseTeamMember::where('user_id', $user->id)
            ->pluck('application_id');

        $teamMemberCaseIds = $teamMemberApplicationIds->isEmpty()
            ? collect()
            : CaseApplication::whereIn('id', $teamMemberApplicationIds)->pluck('case_id');

        return $leaderCaseIds
            ->merge($teamMemberCaseIds)
            ->unique()
            ->values();
    }

    /**
     * Create a new case
     */
    public function createCase(array $data, ?User $user = null): CaseModel
    {
        return DB::transaction(function () use ($data, $user) {
            // If user is provided, use its ID (for partner users)
            if ($user) {
                $data['user_id'] = $user->id;
            }

            // Create case
            $case = CaseModel::create([
                'user_id' => $data['user_id'] ?? null,
                'simulator_id' => $data['simulator_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'reward' => $data['reward'] ?? '',
                'required_team_size' => $data['required_team_size'] ?? 1,
                'deadline' => $data['deadline'] ?? null,
                'status' => $data['status'] ?? 'draft',
            ]);

            // Sync required skills if provided
            if (isset($data['required_skills']) && is_array($data['required_skills'])) {
                $case->skills()->sync($data['required_skills']);
            }

            // Schedule job to update status by deadline if case is active
            $this->scheduleStatusUpdateJob($case);

            return $case->fresh('skills', 'partnerUser.partnerProfile');
        });
    }

    /**
     * Create case for specific partner user
     */
    public function createCaseForPartner(User $user, array $data): CaseModel
    {
        return $this->createCase($data, $user);
    }

    /**
     * Update existing case
     */
    public function updateCase(CaseModel $case, array $data): CaseModel
    {
        return DB::transaction(function () use ($case, $data) {
            $oldDeadline = $case->deadline;
            $oldStatus = $case->status;

            // Update case fields
            $case->update([
                'title' => $data['title'] ?? $case->title,
                'description' => $data['description'] ?? $case->description,
                'reward' => $data['reward'] ?? $case->reward,
                'required_team_size' => $data['required_team_size'] ?? $case->required_team_size,
                'deadline' => $data['deadline'] ?? $case->deadline,
                'status' => $data['status'] ?? $case->status,
                'simulator_id' => $data['simulator_id'] ?? $case->simulator_id,
            ]);

            // Sync skills if provided
            if (isset($data['required_skills']) && is_array($data['required_skills'])) {
                $case->skills()->sync($data['required_skills']);
            }

            // If deadline or status changed, update scheduled job
            $deadlineChanged = $oldDeadline?->format('Y-m-d') !== $case->deadline?->format('Y-m-d');
            $statusChanged = $oldStatus !== $case->status;

            if ($deadlineChanged || $statusChanged) {
                // Cancel old job if exists
                $this->cancelStatusUpdateJob($case->id);

                // Schedule new job if case is active
                $this->scheduleStatusUpdateJob($case);
            }

            return $case->fresh('skills', 'partnerUser.partnerProfile');
        });
    }

    /**
     * Delete case
     */
    public function deleteCase(CaseModel $case): bool
    {
        // Check for active applications
        $activeApplications = $case->applications()
            ->where(function ($q) {
                $q->pending()->orWhere(function ($subQ) {
                    $subQ->accepted();
                });
            })
            ->count();

        if ($activeApplications > 0) {
            throw new \Exception('Cannot delete case with active applications');
        }

        return DB::transaction(function () use ($case) {
            // Cancel scheduled job if exists
            $this->cancelStatusUpdateJob($case->id);

            // Detach skills
            $case->skills()->detach();

            // Delete case (cascade handles applications via DB)
            return $case->delete();
        });
    }

    /**
     * Archive case
     */
    public function archiveCase(CaseModel $case): CaseModel
    {
        // Cancel scheduled job if exists
        $this->cancelStatusUpdateJob($case->id);

        $case->update(['status' => 'archived']);

        return $case->fresh();
    }

    /**
     * Get filtered cases with pagination
     */
    public function getFilteredCases(array $filters): LengthAwarePaginator
    {
        $caseFilter = new CaseFilter($filters);

        $query = CaseModel::query()
            ->with(['partnerUser.partnerProfile', 'skills']);

        $query = $caseFilter->apply($query);

        $pagination = $caseFilter->getPaginationParams();

        return $query->paginate($pagination['per_page']);
    }

    /**
     * Get available cases for student
     */
    public function getAvailableCasesForStudent(User $user, array $filters): LengthAwarePaginator
    {
        $query = CaseModel::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>', now());
            });

        // Exclude cases where student already has application
        $appliedCaseIds = $user->caseApplications()->pluck('case_id');
        $query->whereNotIn('id', $appliedCaseIds);

        // Apply skill filter
        $skills = FilterHelper::getArrayFilter($filters['skills'] ?? null);
        if (! empty($skills)) {
            $query->whereHas('skills', function ($q) use ($skills) {
                $q->whereIn('skills.id', $skills);
            });
        }

        // Apply partner filter
        $userId = FilterHelper::getIntegerFilter($filters['partner_id'] ?? $filters['user_id'] ?? null);
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Apply search filter
        $search = FilterHelper::getStringFilter($filters['search'] ?? null);
        if ($search) {
            $sanitizedSearch = FilterHelper::sanitizeSearch($search);
            $query->where(function ($q) use ($sanitizedSearch) {
                $q->where('title', 'like', "%{$sanitizedSearch}%")
                    ->orWhere('description', 'like', "%{$sanitizedSearch}%");
            });
        }

        // Eager load relationships
        $query->with(['partnerUser.partnerProfile', 'skills']);

        // Get pagination parameters
        $pagination = FilterHelper::getPaginationParams($filters, 12);

        return $query->latest()->paginate($pagination['per_page']);
    }

    /**
     * Get cases for specific partner user
     */
    public function getPartnerCases(User $user, array $filters): LengthAwarePaginator
    {
        $query = CaseModel::where('user_id', $user->id);

        // Apply status filter
        $status = FilterHelper::getStringFilter($filters['status'] ?? null);
        if ($status) {
            $query->where('status', $status);
        }

        // Apply search filter
        $search = FilterHelper::getStringFilter($filters['search'] ?? null);
        if ($search) {
            $sanitizedSearch = FilterHelper::sanitizeSearch($search);
            $query->where(function ($q) use ($sanitizedSearch) {
                $q->where('title', 'like', "%{$sanitizedSearch}%")
                    ->orWhere('description', 'like', "%{$sanitizedSearch}%");
            });
        }

        // Eager load relationships
        $query->with(['skills', 'simulator']);

        // Get pagination parameters
        $pagination = FilterHelper::getPaginationParams($filters, 15);

        return $query->latest()->paginate($pagination['per_page']);
    }

    /**
     * Get active cases for partner user
     */
    public function getActiveCasesForPartner(User $user): Collection
    {
        if (! $user->hasRole('partner')) {
            return collect();
        }

        return CaseModel::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['skills', 'partnerUser.partnerProfile'])
            ->latest()
            ->get();
    }

    /**
     * Get active cases for student (as leader or team member)
     */
    public function getActiveCasesForStudent(User $user, int $limit = 5): Collection
    {
        // Get case IDs where student is leader with accepted application
        $leaderCaseIds = $user->caseApplications()
            ->accepted()
            ->pluck('case_id');

        // Get case IDs where student is team member with accepted application
        $teamMemberApplicationIds = CaseTeamMember::where('user_id', $user->id)
            ->pluck('application_id');

        $teamMemberCaseIds = CaseApplication::whereIn('id', $teamMemberApplicationIds)
            ->accepted()
            ->pluck('case_id');

        // Merge and get unique case IDs
        $caseIds = $leaderCaseIds->merge($teamMemberCaseIds)->unique();

        if ($caseIds->isEmpty()) {
            return collect();
        }

        return CaseModel::whereIn('id', $caseIds)
            ->where('status', 'active')
            ->with(['partnerUser.partnerProfile', 'skills'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recommended cases for student based on skills
     */
    public function getRecommendedCases(User $user, int $limit = 5): Collection
    {
        $studentSkillIds = $user->skills()->pluck('skills.id')->toArray();
        $appliedCaseIds = $this->getAppliedCaseIdsForUser($user);

        if (empty($studentSkillIds)) {
            // Return random active cases if student has no skills
            return CaseModel::where('status', 'active')
                ->whereNotIn('id', $appliedCaseIds)
                ->where(function ($q) {
                    $q->whereNull('deadline')
                        ->orWhere('deadline', '>=', now());
                })
                ->with(['partnerUser.partnerProfile', 'skills'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        // Find cases with matching skills
        return CaseModel::where('status', 'active')
            ->whereNotIn('id', $appliedCaseIds)
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            })
            ->whereHas('skills', function ($q) use ($studentSkillIds) {
                $q->whereIn('skills.id', $studentSkillIds);
            })
            ->with(['partnerUser.partnerProfile', 'skills'])
            ->withCount([
                'skills as matching_skills_count' => function ($q) use ($studentSkillIds) {
                    $q->whereIn('skills.id', $studentSkillIds);
                },
            ])
            ->orderByDesc('matching_skills_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get case statistics
     */
    public function getCaseStatistics(CaseModel $case): array
    {
        $applications = $case->applications();

        return [
            'total_applications' => $applications->count(),
            'pending_applications' => (clone $applications)->pending()->count(),
            'accepted_applications' => (clone $applications)->accepted()->count(),
            'rejected_applications' => (clone $applications)->rejected()->count(),
            'total_teams' => (clone $applications)->accepted()->count(),
            'average_team_size' => $this->calculateAverageTeamSize($case),
        ];
    }

    /**
     * Calculate average team size for case
     */
    private function calculateAverageTeamSize(CaseModel $case): float
    {
        $acceptedApplications = $case->applications()
            ->accepted()
            ->with('teamMembers')
            ->get();

        if ($acceptedApplications->isEmpty()) {
            return 0.0;
        }

        $totalMembers = $acceptedApplications->sum(function ($application) {
            return $application->teamMembers->count() + 1; // +1 for leader
        });

        return round($totalMembers / $acceptedApplications->count(), 2);
    }

    /**
     * Schedule job to update case status by deadline
     */
    private function scheduleStatusUpdateJob(CaseModel $case): void
    {
        // Only schedule for active cases with future deadline
        if ($case->status !== 'active' || ! $case->deadline || $case->deadline->isPast()) {
            return;
        }

        try {
            // Dispatch job with delay until deadline
            UpdateCaseStatusByDeadline::dispatch($case->id)
                ->delay($case->deadline);

            // Store deadline in cache for later cancellation check
            // This helps identify which jobs to cancel
            Cache::put("case_status_job_{$case->id}", [
                'deadline' => $case->deadline->format('Y-m-d H:i:s'),
                'scheduled_at' => now()->format('Y-m-d H:i:s'),
            ], $case->deadline->addDays(1));

            Log::info('Scheduled job to update case status by deadline', [
                'case_id' => $case->id,
                'deadline' => $case->deadline->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to schedule case status update job', [
                'case_id' => $case->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cancel scheduled job for case status update
     */
    private function cancelStatusUpdateJob(int $caseId): void
    {
        $cacheKey = "case_status_job_{$caseId}";
        $jobInfo = Cache::get($cacheKey);

        if ($jobInfo) {
            try {
                $uniqueId = "update_case_status_{$caseId}";
                $queueDriver = config('queue.default');

                // Set cancellation flag (Job will check this before execution)
                // This works for all queue drivers (database, redis, sqs, etc.)
                Cache::put("case_status_job_cancelled_{$caseId}", true, now()->addDays(30));

                // For database queue driver: try to delete jobs from database
                if ($queueDriver === 'database') {
                    try {
                        DB::table('jobs')
                            ->where('payload', 'like', "%{$uniqueId}%")
                            ->delete();
                    } catch (\Exception $dbException) {
                        Log::warning('Failed to delete job from database queue', [
                            'case_id' => $caseId,
                            'error' => $dbException->getMessage(),
                        ]);
                        // Continue - cancellation flag is set, so job won't execute
                    }
                }
                // For Redis queue driver: try to remove from delayed queue
                elseif ($queueDriver === 'redis') {
                    try {
                        $redis = Redis::connection();
                        $queueName = config('queue.connections.redis.queue', 'default');
                        
                        // Laravel stores delayed jobs in sorted sets with key: queues:{queue}:delayed
                        $delayedKey = "queues:{$queueName}:delayed";
                        
                        // Get all delayed jobs
                        $delayedJobs = $redis->zrange($delayedKey, 0, -1, 'WITHSCORES');
                        
                        foreach ($delayedJobs as $job => $score) {
                            // Decode job payload (Laravel uses base64 encoding)
                            $decoded = json_decode(base64_decode($job), true);
                            
                            // Check if this is our job
                            if (isset($decoded['data']['commandName']) 
                                && str_contains($decoded['data']['commandName'], 'UpdateCaseStatusByDeadline')
                                && isset($decoded['data']['command']['caseId'])
                                && $decoded['data']['command']['caseId'] === $caseId) {
                                // Remove job from delayed queue
                                $redis->zrem($delayedKey, $job);
                                break;
                            }
                        }
                    } catch (\Exception $redisException) {
                        Log::warning('Failed to remove job from Redis delayed queue', [
                            'case_id' => $caseId,
                            'error' => $redisException->getMessage(),
                        ]);
                        // Continue - cancellation flag is set, so job won't execute
                    }
                }

                // Remove job info from cache
                Cache::forget($cacheKey);

                Log::info('Cancelled scheduled job for case status update', [
                    'case_id' => $caseId,
                    'unique_id' => $uniqueId,
                    'queue_driver' => $queueDriver,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to cancel case status update job', [
                    'case_id' => $caseId,
                    'error' => $e->getMessage(),
                ]);
                
                // Always set cancellation flag as fallback
                Cache::put("case_status_job_cancelled_{$caseId}", true, now()->addDays(30));
                Cache::forget($cacheKey);
            }
        }
    }
}
