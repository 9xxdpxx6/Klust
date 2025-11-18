<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Simulator\CompleteRequest;
use App\Models\Simulator;
use App\Models\SimulatorSession;
use App\Services\ProgressLogService;
use App\Services\SimulatorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SimulatorsController extends Controller
{
    public function __construct(
        private SimulatorService $simulatorService,
        private ProgressLogService $progressLogService
    ) {
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Список доступных симуляторов
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Получить доступные симуляторы (is_active = true)
        $simulators = $this->simulatorService->getAvailableSimulators();

        // Получить историю сессий студента
        $sessions = $this->simulatorService->getStudentSessions($user);

        return Inertia::render('Client/Student/Simulators/Index', [
            'simulators' => $simulators,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Запуск симулятора (создание сессии)
     */
    public function start(Simulator $simulator): RedirectResponse
    {
        $user = auth()->user();

        // Проверить, что симулятор активен
        if (! $simulator->is_active) {
            return redirect()
                ->route('student.simulators.index')
                ->with('error', 'Симулятор недоступен');
        }

        // Проверить, нет ли активной сессии
        if ($this->simulatorService->hasActiveSession($user, $simulator)) {
            return redirect()
                ->route('student.simulators.index')
                ->with('error', 'У вас уже есть активная сессия этого симулятора');
        }

        // Создать сессию
        $session = $this->simulatorService->startSession($user, $simulator);

        return redirect()
            ->route('student.simulators.session', $session)
            ->with('success', 'Симулятор запущен');
    }

    /**
     * Продолжение сессии
     */
    public function session(SimulatorSession $session): Response
    {
        $this->authorize('view', $session);

        // Загрузить симулятор и данные сессии
        $session->load(['simulator']);

        return Inertia::render('Client/Student/Simulators/Session', [
            'session' => $session,
        ]);
    }

    /**
     * Завершение и сохранение результатов
     */
    public function complete(CompleteRequest $request, SimulatorSession $session): RedirectResponse
    {
        $this->authorize('update', $session);

        // Завершить сессию и начислить очки
        $this->simulatorService->completeSession($session, $request->validated());

        // Обновить прогресс студента
        $this->progressLogService->logSimulatorCompletion($session);

        return redirect()
            ->route('student.simulators.index')
            ->with('success', 'Симулятор успешно завершен');
    }
}
