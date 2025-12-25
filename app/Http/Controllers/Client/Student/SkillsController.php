<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Services\ProgressLogService;
use App\Services\SkillService;
use Inertia\Inertia;
use Inertia\Response;

class SkillsController extends Controller
{
    public function __construct(
        private SkillService $skillService,
        private ProgressLogService $progressLogService
    ) {
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Навыки студента
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Получить все навыки студента с уровнями
        $skills = $this->skillService->getStudentSkills($user);

        // Вычислить средний уровень (округляем до целых)
        $averageLevel = 0;
        if ($skills->isNotEmpty()) {
            $totalLevel = $skills->sum(fn($skill) => $skill['level']);
            $averageLevel = (int) round($totalLevel / $skills->count());
        }

        // Получить историю получения очков
        $history = $this->progressLogService->getSkillProgressHistory($user);

        return Inertia::render('Client/Student/Skills/Index', [
            'skills' => $skills,
            'averageLevel' => $averageLevel,
            'maxLevelInSystem' => $this->skillService->getMaxLevelInSystem(),
            'progressHistory' => $history->map(function ($log) {
                $skill = $log->loggable;
                
                // Формируем описание с учетом наличия навыка
                $description = match ($log->action) {
                    'simulator_completion' => $skill 
                        ? "Получено {$log->points_earned} очков в навыке {$skill->name} за прохождение симулятора"
                        : "Получено {$log->points_earned} очков за прохождение симулятора",
                    'case_completion' => $skill
                        ? "Получено {$log->points_earned} очков в навыке {$skill->name} за выполнение кейса"
                        : "Получено {$log->points_earned} очков за выполнение кейса",
                    'manual' => $skill
                        ? "Начислено {$log->points_earned} очков в навыке {$skill->name} вручную"
                        : "Начислено {$log->points_earned} очков вручную",
                    default => $skill
                        ? "Получено {$log->points_earned} очков в навыке {$skill->name}"
                        : "Получено {$log->points_earned} очков",
                };

                return [
                    'id' => $log->id,
                    'points_earned' => $log->points_earned,
                    'action' => $log->action,
                    'created_at' => $log->created_at,
                    'skill' => $skill ? [
                        'id' => $skill->id,
                        'name' => $skill->name,
                    ] : null,
                    'description' => $description,
                ];
            }),
        ]);
    }
}
