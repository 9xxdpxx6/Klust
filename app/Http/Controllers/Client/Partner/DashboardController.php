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
        try {
            $user = auth()->user();

            // Получить статистику партнера
            $statistics = $this->partnerService->getDashboardStatistics($user);

            // Получить активные кейсы
            $activeCases = $this->caseService->getActiveCasesForPartner($user);

            // Получить последние активности (новые заявки, завершенные кейсы)
            $recentActivities = $this->partnerService->getRecentActivities($user);

            return Inertia::render('Client/Partner/Dashboard', [
                'statistics' => $statistics,
                'activeCases' => $activeCases,
                'recentActivities' => $recentActivities,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Dashboard', [
                'statistics' => [],
                'activeCases' => [],
                'recentActivities' => [],
                'error' => 'Ошибка при загрузке данных: ' . $e->getMessage(),
            ]);
        }
    }
}

