<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Exports\ApplicationsExport;
use App\Exports\CasesExport;
use App\Exports\TeamsExport;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Analytics\IndexRequest;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

            // Получить аналитику через AnalyticsService::getPartnerAnalytics($user, $request->validated())
            $analytics = $this->analyticsService->getPartnerAnalytics($user, $request->validated());

            return Inertia::render('Client/Partner/Analytics/Index', [
                'analytics' => $analytics,
                'filters' => $request->only(['period', 'date_from', 'date_to']),
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Analytics/Index', [
                'analytics' => [],
                'filters' => $request->only(['period', 'date_from', 'date_to']),
                'error' => 'Ошибка при загрузке аналитики: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Экспорт кейсов партнера в Excel
     */
    public function exportCases(Request $request): BinaryFileResponse
    {
        $user = auth()->user();

        $filters = $this->prepareFilters($request);
        $filters['partner_id'] = $user->id;

        $filename = 'cases_'.date('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new CasesExport($filters), $filename);
    }

    /**
     * Экспорт всех заявок партнера в Excel
     */
    public function exportApplications(Request $request): BinaryFileResponse
    {
        $user = auth()->user();

        $filters = $this->prepareFilters($request);
        $filters['partner_id'] = $user->id;

        $filename = 'applications_'.date('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new ApplicationsExport($filters), $filename);
    }

    /**
     * Экспорт команд партнера в Excel
     */
    public function exportTeams(Request $request): BinaryFileResponse
    {
        $user = auth()->user();

        $filters = $this->prepareFilters($request);

        $filename = 'teams_'.date('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new TeamsExport($user->id, $filters), $filename);
    }

    /**
     * Подготовить фильтры из запроса (преобразовать period в date_from/date_to)
     */
    private function prepareFilters(Request $request): array
    {
        $filters = [];

        $period = $request->input('period');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Если указан период, вычисляем даты
        if ($period && $period !== 'all') {
            $days = (int) $period;
            $dateTo = Carbon::now()->toDateString();
            $dateFrom = Carbon::now()->subDays($days)->toDateString();
        }

        // Используем явные даты или вычисленные из периода
        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        return $filters;
    }
}
