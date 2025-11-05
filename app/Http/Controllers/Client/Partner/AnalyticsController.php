<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Analytics\IndexRequest;
use App\Services\AnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

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
        $user = auth()->user();
        $partner = $user->partner;

        // Получить аналитику через AnalyticsService::getPartnerAnalytics($partner, $request->validated())
        $analytics = $this->analyticsService->getPartnerAnalytics($partner, $request->validated());

        return Inertia::render('Client/Partner/Analytics/Index', [
            'analytics' => $analytics,
        ]);
    }
}

