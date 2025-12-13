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

        // Получить историю получения очков
        $history = $this->progressLogService->getSkillProgressHistory($user);

        return Inertia::render('Client/Student/Skills/Index', [
            'skills' => $skills,
            'progressHistory' => $history->map(function ($log) {
                $skill = $log->loggable;
                $description = match ($log->action) {
                    'simulator_completion' => "Получено {$log->points_earned} очков за прохождение симулятора",
                    'case_completion' => "Получено {$log->points_earned} очков за выполнение кейса",
                    'manual' => "Начислено {$log->points_earned} очков вручную",
                    default => "Получено {$log->points_earned} очков",
                };

                if ($skill) {
                    $description = "Получено {$log->points_earned} очков в навыке {$skill->name}";
                }

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
