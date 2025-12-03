<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseModel;
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
        $activeAsLeader = $user->caseApplications()
            ->accepted()
            ->count();

        $activeAsTeamMember = CaseTeamMember::where('user_id', $user->id)
            ->whereHas('application', function ($q) {
                $q->accepted();
            })
            ->count();

        // Get completed cases
        $completedAsLeader = $user->caseApplications()
            ->completed()
            ->count();

        $completedAsTeamMember = CaseTeamMember::where('user_id', $user->id)
            ->whereHas('application', function ($q) {
                $q->completed();
            })
            ->count();

        // Get pending applications
        $pendingApplications = $user->caseApplications()
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

    /**
     * Get recent achievements for student (badges, skills, completed cases)
     */
    public function getRecentAchievements(User $user): array
    {
        // Get recent badges (last 10)
        $recentBadges = $user->badges()
            ->orderByPivot('earned_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($badge) {
                // Get icon path - if icon is a file, return storage path, otherwise null
                $iconPath = null;
                if ($badge->icon && !str_starts_with($badge->icon, 'pi-') && !str_starts_with($badge->icon, 'fa-')) {
                    $iconPath = '/storage/'.$badge->icon;
                }

                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'icon_path' => $iconPath,
                    'image' => $iconPath, // For backward compatibility with Vue component
                    'earned_at' => $badge->pivot->earned_at 
                        ? (\Carbon\Carbon::parse($badge->pivot->earned_at)->toISOString())
                        : null,
                ];
            });

        // Get recent skills with level (last 10)
        $recentSkills = $user->skills()
            ->orderByPivot('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'description' => $skill->description,
                    'level' => $skill->pivot->level ?? 1,
                    'points_earned' => $skill->pivot->points_earned ?? 0,
                ];
            });

        // Get completed cases (as leader or team member)
        $completedCaseIds = $user->caseApplications()
            ->completed()
            ->pluck('case_id');

        $completedCaseIdsAsMember = CaseTeamMember::where('user_id', $user->id)
            ->whereHas('application', function ($q) {
                $q->completed();
            })
            ->with('application.case')
            ->get()
            ->pluck('application.case_id')
            ->filter();

        $allCompletedCaseIds = $completedCaseIds->merge($completedCaseIdsAsMember)->unique();

        $completedCases = collect();
        if ($allCompletedCaseIds->isNotEmpty()) {
            $completedCases = CaseModel::whereIn('id', $allCompletedCaseIds)
                ->with(['partner'])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($case) use ($user) {
                    // Get completion date from application
                    $application = $user->caseApplications()
                        ->where('case_id', $case->id)
                        ->completed()
                        ->first();

                    if (!$application) {
                        $teamMember = CaseTeamMember::where('user_id', $user->id)
                            ->whereHas('application', function ($q) use ($case) {
                                $q->where('case_id', $case->id)->completed();
                            })
                            ->with('application')
                            ->first();
                        $application = $teamMember?->application;
                    }

                    return [
                        'id' => $case->id,
                        'title' => $case->title,
                        'description' => $case->description,
                        'partner_name' => $case->partner?->company_name,
                        'completed_at' => $application?->reviewed_at?->toISOString() ?? $case->updated_at->toISOString(),
                    ];
                });
        }

        return [
            'badges' => $recentBadges,
            'skills' => $recentSkills,
            'completedCases' => $completedCases,
        ];
    }
}
