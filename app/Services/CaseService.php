<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CaseService
{
    /**
     * Create a new case
     */
    public function createCase(array $data, ?Partner $partner = null): CaseModel
    {
        return DB::transaction(function () use ($data, $partner) {
            // If partner is provided, use its ID
            if ($partner) {
                $data['partner_id'] = $partner->id;
            }

            // Create case
            $case = CaseModel::create([
                'partner_id' => $data['partner_id'],
                'simulator_id' => $data['simulator_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'required_team_size' => $data['required_team_size'] ?? 1,
                'deadline' => $data['deadline'] ?? null,
                'status' => $data['status'] ?? 'draft',
            ]);

            // Sync required skills if provided
            if (isset($data['required_skills']) && is_array($data['required_skills'])) {
                $case->skills()->sync($data['required_skills']);
            }

            return $case->fresh('skills', 'partner');
        });
    }

    /**
     * Create case for specific partner
     */
    public function createCaseForPartner(Partner $partner, array $data): CaseModel
    {
        return $this->createCase($data, $partner);
    }

    /**
     * Update existing case
     */
    public function updateCase(CaseModel $case, array $data): CaseModel
    {
        return DB::transaction(function () use ($case, $data) {
            // Update case fields
            $case->update([
                'title' => $data['title'] ?? $case->title,
                'description' => $data['description'] ?? $case->description,
                'required_team_size' => $data['required_team_size'] ?? $case->required_team_size,
                'deadline' => $data['deadline'] ?? $case->deadline,
                'status' => $data['status'] ?? $case->status,
                'simulator_id' => $data['simulator_id'] ?? $case->simulator_id,
            ]);

            // Sync skills if provided
            if (isset($data['required_skills']) && is_array($data['required_skills'])) {
                $case->skills()->sync($data['required_skills']);
            }

            return $case->fresh('skills', 'partner');
        });
    }

    /**
     * Delete case
     */
    public function deleteCase(CaseModel $case): bool
    {
        // Check for active applications
        $activeApplications = $case->applications()
            ->whereIn('status', ['pending', 'accepted'])
            ->count();

        if ($activeApplications > 0) {
            throw new \Exception('Cannot delete case with active applications');
        }

        return DB::transaction(function () use ($case) {
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
        $case->update(['status' => 'archived']);

        return $case->fresh();
    }

    /**
     * Get filtered cases with pagination
     */
    public function getFilteredCases(array $filters): LengthAwarePaginator
    {
        $query = CaseModel::query();

        // Apply status filter
        if (isset($filters['status']) && ! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply partner filter
        if (isset($filters['partner_id']) && ! empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }

        // Apply search filter
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Eager load relationships
        $query->with(['partner', 'skills']);

        return $query->latest()->paginate(20);
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
        $appliedCaseIds = $user->caseApplicationsAsLeader()->pluck('case_id');
        $query->whereNotIn('id', $appliedCaseIds);

        // Apply skill filter
        if (isset($filters['skills']) && is_array($filters['skills']) && ! empty($filters['skills'])) {
            $query->whereHas('skills', function ($q) use ($filters) {
                $q->whereIn('skills.id', $filters['skills']);
            });
        }

        // Apply partner filter
        if (isset($filters['partner_id']) && ! empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }

        // Apply search filter
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Eager load relationships
        $query->with(['partner', 'skills']);

        return $query->latest()->paginate(12);
    }

    /**
     * Get cases for specific partner
     */
    public function getPartnerCases(Partner $partner, array $filters): LengthAwarePaginator
    {
        $query = CaseModel::where('partner_id', $partner->id);

        // Apply status filter
        if (isset($filters['status']) && ! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply search filter
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Eager load relationships
        $query->with(['skills', 'simulator']);

        return $query->latest()->paginate(15);
    }

    /**
     * Get active cases for partner user
     */
    public function getActiveCasesForPartner(User $user): Collection
    {
        $partner = $user->partner;

        if (! $partner) {
            return collect();
        }

        return CaseModel::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->with(['skills', 'partner'])
            ->latest()
            ->get();
    }

    /**
     * Get recommended cases for student based on skills
     */
    public function getRecommendedCases(User $user, int $limit = 5): Collection
    {
        $studentSkillIds = $user->skills()->pluck('skills.id')->toArray();

        if (empty($studentSkillIds)) {
            // Return random active cases if student has no skills
            return CaseModel::where('status', 'active')
                ->with(['partner', 'skills'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        // Get applied case IDs to exclude
        $appliedCaseIds = $user->caseApplicationsAsLeader()->pluck('case_id');

        // Find cases with matching skills
        return CaseModel::where('status', 'active')
            ->whereNotIn('id', $appliedCaseIds)
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>', now());
            })
            ->whereHas('skills', function ($q) use ($studentSkillIds) {
                $q->whereIn('skills.id', $studentSkillIds);
            })
            ->with(['partner', 'skills'])
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
            'pending_applications' => $applications->where('status', 'pending')->count(),
            'accepted_applications' => $applications->where('status', 'accepted')->count(),
            'rejected_applications' => $applications->where('status', 'rejected')->count(),
            'total_teams' => $applications->where('status', 'accepted')->count(),
            'average_team_size' => $this->calculateAverageTeamSize($case),
        ];
    }

    /**
     * Ensure case belongs to partner
     *
     * @throws \Exception
     */
    public function ensureCaseBelongsToPartner(CaseModel $case, Partner $partner): void
    {
        if ($case->partner_id !== $partner->id) {
            throw new \Exception('This case does not belong to the partner');
        }
    }

    /**
     * Calculate average team size for case
     */
    private function calculateAverageTeamSize(CaseModel $case): float
    {
        $acceptedApplications = $case->applications()
            ->where('status', 'accepted')
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
}
