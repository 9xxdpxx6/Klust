<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Exports\ApplicationsExport;
use App\Exports\CasesExport;
use App\Exports\TeamsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Analytics\IndexRequest;
use App\Services\AnalyticsService;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {
        $this->middleware(['auth', 'role:partner']);
    }

    /**
     * Аналитика партнера
     */
    public function index(IndexRequest $request): Response
    {
        try {
            $user = auth()->user();
            $partner = $user->partner;

            // Получить аналитику через AnalyticsService::getPartnerAnalytics($partner, $request->validated())
            $analytics = $this->analyticsService->getPartnerAnalytics($partner, $request->validated());

            return Inertia::render('Client/Partner/Analytics/Index', [
                'analytics' => $analytics,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Analytics/Index', [
                'analytics' => [],
                'error' => 'Ошибка при загрузке аналитики: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Экспорт кейсов партнера в Excel
     */
    public function exportCases(): BinaryFileResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        $filename = 'cases_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new CasesExport(['partner_id' => $partner->id]), $filename);
    }

    /**
     * Экспорт всех заявок партнера в Excel
     */
    public function exportApplications(): BinaryFileResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        $filename = 'applications_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new ApplicationsExport(['partner_id' => $partner->id]), $filename);
    }

    /**
     * Экспорт команд партнера в Excel
     */
    public function exportTeams(): BinaryFileResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        $filename = 'teams_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new TeamsExport($partner->id), $filename);
    }
}

