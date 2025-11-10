<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Partner;
use Illuminate\Support\Carbon;

class AnalyticsService
{
    /**
     * Get analytics for partner
     */
    public function getPartnerAnalytics(Partner $partner, array $filters): array
    {
        // Parse date filters
        $dateFrom = isset($filters['date_from'])
            ? Carbon::parse($filters['date_from'])
            : Carbon::now()->subMonths(6);

        $dateTo = isset($filters['date_to'])
            ? Carbon::parse($filters['date_to'])
            : Carbon::now();

        // Filter by specific case if provided
        $caseId = $filters['case_id'] ?? null;

        // Get cases query
        $casesQuery = $partner->cases()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($caseId) {
            $casesQuery->where('id', $caseId);
        }

        $cases = $casesQuery->with(['applications', 'skills'])->get();

        // Calculate overview statistics
        $overview = $this->calculateOverviewStatistics($cases);

        // Calculate application statistics
        $applicationStats = $this->calculateApplicationStatistics($cases);

        // Build chart data
        $charts = [
            'applications_over_time' => $this->buildApplicationsOverTimeChart($cases, $dateFrom, $dateTo),
            'cases_by_status' => $this->buildCasesByStatusChart($cases),
            'application_conversion' => $this->buildApplicationConversionChart($cases),
            'popular_skills' => $this->buildPopularSkillsChart($cases),
        ];

        return [
            'overview' => $overview,
            'application_stats' => $applicationStats,
            'charts' => $charts,
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
     */
    private function calculateOverviewStatistics($cases): array
    {
        $totalCases = $cases->count();
        $activeCases = $cases->where('status', 'active')->count();
        $completedCases = $cases->where('status', 'completed')->count();

        $totalApplications = $cases->sum(function ($case) {
            return $case->applications->count();
        });

        $acceptedApplications = $cases->sum(function ($case) {
            return $case->applications->where('status', 'accepted')->count();
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

        $avgResponseTime = $allApplications
            ->filter(fn ($app) => $app->reviewed_at !== null)
            ->avg(function ($app) {
                return $app->submitted_at->diffInHours($app->reviewed_at);
            });

        $avgTeamSize = $allApplications
            ->where('status', 'accepted')
            ->avg(function ($app) {
                return $app->teamMembers()->count() + 1; // +1 for leader
            });

        return [
            'pending' => $allApplications->where('status', 'pending')->count(),
            'accepted' => $allApplications->where('status', 'accepted')->count(),
            'rejected' => $allApplications->where('status', 'rejected')->count(),
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

        return [
            'labels' => array_map(fn ($key) => Carbon::parse($key)->format('M Y'), array_keys($months)),
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
            'labels' => ['Active', 'Completed', 'Draft', 'Archived'],
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

        $pending = $allApplications->where('status', 'pending')->count();
        $accepted = $allApplications->where('status', 'accepted')->count();
        $rejected = $allApplications->where('status', 'rejected')->count();

        return [
            'labels' => ['Pending', 'Accepted', 'Rejected'],
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
}
