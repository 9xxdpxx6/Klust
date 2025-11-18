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
            'history' => $history,
        ]);
    }
}
