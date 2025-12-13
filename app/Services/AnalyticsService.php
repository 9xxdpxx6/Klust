<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\FilterHelper;
use App\Models\ApplicationStatus;
use App\Models\User;
use Illuminate\Support\Carbon;

class AnalyticsService
{
    /**
     * Get analytics for partner user
     */
    public function getPartnerAnalytics(User $user, array $filters): array
    {
        // Handle period filter - if period is set, calculate dates from it
        $period = $filters['period'] ?? null;
        $dateFrom = null;
        $dateTo = null;

        if ($period && $period !== 'all') {
            // Calculate dates based on period
            $days = (int) $period;
            $dateTo = Carbon::now();
            $dateFrom = Carbon::now()->subDays($days);
        } else {
            // Use explicit date filters or defaults
            $dateFrom = FilterHelper::getDateFilter(
                $filters['date_from'] ?? null,
                Carbon::now()->subMonths(6)
            );

            $dateTo = FilterHelper::getDateFilter(
                $filters['date_to'] ?? null,
                Carbon::now()
            );
        }

        // Filter by specific case if provided
        $caseId = FilterHelper::getIntegerFilter($filters['case_id'] ?? null);

        // Get cases query
        $casesQuery = \App\Models\CaseModel::where('user_id', $user->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($caseId) {
            $casesQuery->where('id', $caseId);
        }

        $cases = $casesQuery->with(['applications.status', 'skills'])->get();

        // Get status IDs once for reuse
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');

        // Calculate overview statistics
        $overview = $this->calculateOverviewStatistics($cases, $acceptedStatusId);

        // Calculate application statistics
        $applicationStats = $this->calculateApplicationStatistics($cases);

        // Build chart data
        $charts = [
            'applications_over_time' => $this->buildApplicationsOverTimeChart($cases, $dateFrom, $dateTo),
            'cases_by_status' => $this->buildCasesByStatusChart($cases),
            'application_conversion' => $this->buildApplicationConversionChart($cases),
            'popular_skills' => $this->buildPopularSkillsChart($cases),
        ];

        // Calculate top cases
        $topCases = $this->getTopCases($cases, $acceptedStatusId);

        // Calculate total teams
        $totalTeams = $cases->sum(function ($case) use ($acceptedStatusId) {
            return $case->applications->where('status_id', $acceptedStatusId)->count();
        });

        // Add total_teams to overview
        $overview['total_teams'] = $totalTeams;

        return [
            'overview' => $overview,
            'application_stats' => $applicationStats,
            'charts' => $charts,
            'top_cases' => $topCases,
            'date_range' => [
                'from' => $dateFrom->toDateString(),
                'to' => $dateTo->toDateString(),
            ],
        ];
    }

    /**
     * Calculate overview statistics
     *
     * @param  \Illuminate\Support\Collection  $cases
     * @param  int  $acceptedStatusId
     */
    private function calculateOverviewStatistics($cases, int $acceptedStatusId): array
    {
        $totalCases = $cases->count();
        $activeCases = $cases->where('status', 'active')->count();
        $completedCases = $cases->where('status', 'completed')->count();

        $totalApplications = $cases->sum(function ($case) {
            return $case->applications->count();
        });

        $acceptedApplications = $cases->sum(function ($case) use ($acceptedStatusId) {
            return $case->applications->where('status_id', $acceptedStatusId)->count();
        });

        $conversionRate = $totalApplications > 0
            ? round(($acceptedApplications / $totalApplications) * 100, 2)
            : 0;

        return [
            'total_cases' => $totalCases,
            'active_cases' => $activeCases,
            'completed_cases' => $completedCases,
            'total_applications' => $totalApplications,
            'accepted_applications' => $acceptedApplications,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Calculate application statistics
     *
     * @param  \Illuminate\Support\Collection  $cases
     */
    private function calculateApplicationStatistics($cases): array
    {
        $allApplications = $cases->flatMap(fn ($case) => $case->applications);

        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');
        $rejectedStatusId = ApplicationStatus::getIdByName('rejected');

        $avgResponseTime = $allApplications
            ->filter(fn ($app) => $app->reviewed_at !== null)
            ->avg(function ($app) {
                return $app->submitted_at->diffInHours($app->reviewed_at);
            });

        $avgTeamSize = $allApplications
            ->where('status_id', $acceptedStatusId)
            ->avg(function ($app) {
                return $app->teamMembers()->count() + 1; // +1 for leader
            });

        return [
            'pending' => $allApplications->where('status_id', $pendingStatusId)->count(),
            'accepted' => $allApplications->where('status_id', $acceptedStatusId)->count(),
            'rejected' => $allApplications->where('status_id', $rejectedStatusId)->count(),
            'avg_response_time_hours' => round($avgResponseTime ?? 0, 2),
            'avg_team_size' => round($avgTeamSize ?? 0, 2),
        ];
    }

    /**
     * Build applications over time chart
     *
     * @param  \Illuminate\Support\Collection  $cases
     */
    private function buildApplicationsOverTimeChart($cases, Carbon $dateFrom, Carbon $dateTo): array
    {
        $allApplications = $cases->flatMap(fn ($case) => $case->applications);

        // Group by month
        $months = [];
        $current = $dateFrom->copy()->startOfMonth();

        while ($current <= $dateTo) {
            $monthKey = $current->format('Y-m');
            $months[$monthKey] = 0;
            $current->addMonth();
        }

        // Count applications per month
        foreach ($allApplications as $application) {
            $monthKey = $application->submitted_at->format('Y-m');
            if (isset($months[$monthKey])) {
                $months[$monthKey]++;
            }
        }

        // Format months in Russian
        $monthNames = [
            1 => 'янв', 2 => 'фев', 3 => 'мар', 4 => 'апр', 5 => 'май', 6 => 'июн',
            7 => 'июл', 8 => 'авг', 9 => 'сен', 10 => 'окт', 11 => 'ноя', 12 => 'дек',
        ];

        $labels = array_map(function ($key) use ($monthNames) {
            $date = Carbon::parse($key);
            $month = $monthNames[$date->month] ?? $date->format('M');
            return $month.' '.$date->year;
        }, array_keys($months));

        return [
            'labels' => $labels,
            'data' => array_values($months),
        ];
    }

    /**
     * Build cases by status chart
     *
     * @param  \Illuminate\Support\Collection  $cases
     */
    private function buildCasesByStatusChart($cases): array
    {
        return [
            'labels' => ['Активные', 'Завершенные', 'Черновики', 'Архивные'],
            'data' => [
                $cases->where('status', 'active')->count(),
                $cases->where('status', 'completed')->count(),
                $cases->where('status', 'draft')->count(),
                $cases->where('status', 'archived')->count(),
            ],
        ];
    }

    /**
     * Build application conversion chart
     *
     * @param  \Illuminate\Support\Collection  $cases
     */
    private function buildApplicationConversionChart($cases): array
    {
        $allApplications = $cases->flatMap(fn ($case) => $case->applications);

        $pendingStatusId = ApplicationStatus::getIdByName('pending');
        $acceptedStatusId = ApplicationStatus::getIdByName('accepted');
        $rejectedStatusId = ApplicationStatus::getIdByName('rejected');

        $pending = $allApplications->where('status_id', $pendingStatusId)->count();
        $accepted = $allApplications->where('status_id', $acceptedStatusId)->count();
        $rejected = $allApplications->where('status_id', $rejectedStatusId)->count();

        return [
            'labels' => ['На рассмотрении', 'Принята', 'Отклонена'],
            'data' => [$pending, $accepted, $rejected],
        ];
    }

    /**
     * Build popular skills chart
     *
     * @param  \Illuminate\Support\Collection  $cases
     */
    private function buildPopularSkillsChart($cases): array
    {
        $skillCounts = [];

        foreach ($cases as $case) {
            foreach ($case->skills as $skill) {
                $skillName = $skill->name;
                $skillCounts[$skillName] = ($skillCounts[$skillName] ?? 0) + 1;
            }
        }

        // Sort by count and take top 10
        arsort($skillCounts);
        $topSkills = array_slice($skillCounts, 0, 10, true);

        return [
            'labels' => array_keys($topSkills),
            'data' => array_values($topSkills),
        ];
    }

    /**
     * Get top cases by applications count
     *
     * @param  \Illuminate\Support\Collection  $cases
     * @param  int  $acceptedStatusId
     */
    private function getTopCases($cases, int $acceptedStatusId): array
    {
        return $cases
            ->map(function ($case) use ($acceptedStatusId) {
                $applicationsCount = $case->applications->count();
                $teamsCount = $case->applications->where('status_id', $acceptedStatusId)->count();
                $conversionRate = $applicationsCount > 0
                    ? round(($teamsCount / $applicationsCount) * 100, 2)
                    : 0;

                return [
                    'id' => $case->id,
                    'title' => $case->title,
                    'applications_count' => $applicationsCount,
                    'teams_count' => $teamsCount,
                    'conversion_rate' => $conversionRate,
                ];
            })
            ->sortByDesc('applications_count')
            ->take(10)
            ->values()
            ->toArray();
    }
}
