<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * Get general statistics for admin dashboard
     */
    public function getStatistics(): array
    {
        // Define time periods
        $lastMonth = Carbon::now()->subMonth();
        $lastWeek = Carbon::now()->subWeek();

        // Count students
        $totalStudents = User::role('student')->count();
        $newStudentsThisMonth = User::role('student')
            ->where('created_at', '>=', $lastMonth)
            ->count();

        // Count partners
        $totalPartners = Partner::count();
        $newPartnersThisMonth = Partner::where('created_at', '>=', $lastMonth)->count();

        // Count cases by status
        $activeCases = CaseModel::where('status', 'active')->count();
        $completedCases = CaseModel::where('status', 'completed')->count();
        $draftCases = CaseModel::where('status', 'draft')->count();
        $totalCases = CaseModel::count();

        // Count applications
        $pendingApplications = CaseApplication::pending()->count();
        $acceptedApplications = CaseApplication::accepted()->count();
        $totalApplications = CaseApplication::count();

        // Recent activity
        $recentUsers = User::latest()->limit(10)->get();
        $recentCases = CaseModel::with('partner')->latest()->limit(10)->get();
        $recentApplications = CaseApplication::with(['leader', 'case'])
            ->latest()
            ->limit(10)
            ->get();

        return [
            'overview' => [
                'total_students' => $totalStudents,
                'new_students_this_month' => $newStudentsThisMonth,
                'total_partners' => $totalPartners,
                'new_partners_this_month' => $newPartnersThisMonth,
                'total_cases' => $totalCases,
                'active_cases' => $activeCases,
                'completed_cases' => $completedCases,
                'draft_cases' => $draftCases,
                'total_applications' => $totalApplications,
                'pending_applications' => $pendingApplications,
                'accepted_applications' => $acceptedApplications,
            ],
            'recent_activity' => [
                'users' => $recentUsers->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->name,
                    'created_at' => $user->created_at,
                ]),
                'cases' => $recentCases->map(fn ($case) => [
                    'id' => $case->id,
                    'title' => $case->title,
                    'partner' => $case->partner?->company_name,
                    'status' => $case->status,
                    'created_at' => $case->created_at,
                ]),
                'applications' => $recentApplications->map(fn ($app) => [
                    'id' => $app->id,
                    'leader' => $app->leader?->name,
                    'case' => $app->case?->title,
                    'status' => $app->status,
                    'submitted_at' => $app->submitted_at,
                ]),
            ],
            'charts' => [
                'cases_by_status' => [
                    'active' => $activeCases,
                    'completed' => $completedCases,
                    'draft' => $draftCases,
                    'archived' => CaseModel::where('status', 'archived')->count(),
                ],
                'applications_by_status' => [
                    'pending' => $pendingApplications,
                    'accepted' => $acceptedApplications,
                    'rejected' => CaseApplication::rejected()->count(),
                ],
            ],
        ];
    }

    /**
     * Get weekly statistics for charts
     */
    public function getWeeklyStatistics(): array
    {
        $days = [];
        $newUsers = [];
        $newApplications = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $days[] = $date->format('M d');

            $newUsers[] = User::whereDate('created_at', $date)->count();
            $newApplications[] = CaseApplication::whereDate('submitted_at', $date)->count();
        }

        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $newUsers,
                ],
                [
                    'label' => 'New Applications',
                    'data' => $newApplications,
                ],
            ],
        ];
    }
}
