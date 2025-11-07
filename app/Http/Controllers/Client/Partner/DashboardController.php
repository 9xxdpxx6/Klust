<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerService;
use App\Services\CaseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private PartnerService $partnerService,
        private CaseService $caseService
    ) {
        $this->middleware(['auth', 'role:partner']);
    }

    /**
     * Панель партнера
     */
    public function index(): Response
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Получить статистику партнера
        $statistics = $this->partnerService->getDashboardStatistics($user);

        // Получить активные кейсы через getPartnerCases() с фильтром status='active'
        $activeCases = $this->caseService->getPartnerCases($partner, ['status' => 'active']);

        // Получить последние активности (новые заявки, завершенные кейсы)
        $recentActivities = $this->partnerService->getRecentActivities($user);

        return Inertia::render('Client/Partner/Dashboard', [
            'statistics' => $statistics,
            'activeCases' => $activeCases,
            'recentActivities' => $recentActivities,
        ]);
    }
}

