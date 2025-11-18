<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Services\CaseService;
use App\Services\StudentService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private CaseService $caseService
    ) {
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Панель студента
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Получить статистику студента
        $statistics = $this->studentService->getDashboardStatistics($user);

        // Получить активные кейсы студента
        $activeCases = $this->caseService->getActiveCasesForStudent($user);

        // Получить рекомендации
        $recommendations = $this->caseService->getRecommendedCases($user);

        // Получить последние достижения (бейджи, навыки)
        $recentAchievements = $this->studentService->getRecentAchievements($user);

        return Inertia::render('Client/Student/Dashboard', [
            'statistics' => $statistics,
            'activeCases' => $activeCases,
            'recommendations' => $recommendations,
            'recentAchievements' => $recentAchievements,
        ]);
    }
}
