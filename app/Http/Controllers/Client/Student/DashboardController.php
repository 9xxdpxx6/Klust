<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use App\Services\CaseService;
use App\Services\StudentService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private CaseService $caseService,
        private ApplicationService $applicationService
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
        $activeCases = $this->caseService->getActiveCasesForStudent($user, 5);

        // Получить рекомендации
        $recommendations = $this->caseService->getRecommendedCases($user, 5);

        // Добавить информацию о заявках студента для каждого рекомендательного кейса
        foreach ($recommendations as $case) {
            $application = $this->applicationService->getStudentApplicationStatus($user, $case);
            if ($application) {
                if (!$application->relationLoaded('status')) {
                    $application->load('status');
                }
                $case->user_application = [
                    'id' => $application->id,
                    'status' => $application->status->name ?? null,
                    'status_label' => $application->status->label ?? null,
                ];
            } else {
                $case->user_application = null;
            }
        }

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
