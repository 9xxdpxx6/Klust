<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Services\CaseService;
use Inertia\Inertia;
use Inertia\Response;

class GuestController extends Controller
{
    public function __construct(
        private CaseService $caseService
    ) {
    }

    /**
     * Главная страница для гостей
     */
    public function home(): Response
    {
        // Получить статистику для главной страницы
        $statistics = [
            'total_cases' => CaseModel::where('status', 'active')->count(),
            'total_students' => \App\Models\User::role('student')->count(),
            'total_partners' => \App\Models\User::role('partner')->count(),
        ];

        // Получить несколько активных кейсов для примера
        $featuredCases = CaseModel::with(['partner.user', 'skills'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return Inertia::render('Guest/Home', [
            'statistics' => $statistics,
            'featuredCases' => $featuredCases,
        ]);
    }

    /**
     * Страница "О проекте"
     */
    public function about(): Response
    {
        return Inertia::render('Guest/About');
    }

    /**
     * Публичный каталог кейсов для гостей
     */
    public function cases(): Response
    {
        $cases = CaseModel::with(['partner.user', 'skills'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return Inertia::render('Guest/Cases', [
            'cases' => $cases,
        ]);
    }
}
