<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Редирект с /admin на dashboard
     */
    public function redirect(): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    /**
     * Display the admin dashboard
     */
    public function index(): Response
    {
        // TODO: Создать UserPolicy и раскомментировать
        // $this->authorize('viewAny', \App\Models\User::class);

        $statistics = $this->dashboardService->getStatistics();
        $weeklyStats = $this->dashboardService->getWeeklyStatistics();
        $topSkills = $this->dashboardService->getTopSkills(5);
        $studentsByCourse = $this->dashboardService->getStudentsByCourse();

        // Format data for Vue component
        $stats = [
            'totalStudents' => $statistics['overview']['total_students'],
            'totalPartners' => $statistics['overview']['total_partners'],
            'activeCases' => $statistics['overview']['active_cases'],
            'completedCases' => [
                'month' => $statistics['overview']['completed_cases_this_month'],
            ],
        ];

        $recentActivities = [
            'registrations' => $statistics['recent_activity']['users'],
            'createdCases' => $statistics['recent_activity']['cases'],
            'completedCases' => $statistics['recent_activity']['completed_cases'],
        ];

        // Format chart data for PrimeVue Chart component
        $chartData = [
            'registrations' => [
                'labels' => $weeklyStats['labels'],
                'datasets' => $weeklyStats['datasets'],
            ],
            'cases' => [
                'labels' => $this->translateCaseStatusLabels(array_keys($statistics['charts']['cases_by_status'])),
                'datasets' => [
                    [
                        'label' => __('dashboard.charts.cases', [], 'ru'),
                        'data' => array_values($statistics['charts']['cases_by_status']),
                        'backgroundColor' => [
                            '#10B981', // green for active
                            '#3B82F6', // blue for completed
                            '#F59E0B', // amber for draft
                            '#6B7280', // gray for archived
                        ],
                    ],
                ],
            ],
            'topSkills' => $topSkills,
            'studentsByCourse' => $studentsByCourse,
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Translate case status labels to Russian using Laravel Translation
     */
    private function translateCaseStatusLabels(array $labels): array
    {
        return array_map(function ($label) {
            return __("case_statuses.{$label}", [], 'ru') ?: $label;
        }, $labels);
    }
}
