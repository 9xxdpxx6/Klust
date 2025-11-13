<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display the admin dashboard
     */
    public function index(): Response
    {
        // TODO: Создать UserPolicy и раскомментировать
        // $this->authorize('viewAny', \App\Models\User::class);

        $statistics = $this->dashboardService->getStatistics();
        $weeklyStats = $this->dashboardService->getWeeklyStatistics();

        return Inertia::render('Admin/Dashboard', [
            'statistics' => $statistics,
            'weeklyStats' => $weeklyStats,
        ]);
    }
}