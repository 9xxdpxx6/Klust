<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Simulator\CompleteRequest;
use App\Http\Requests\Student\Simulator\UpdateStateRequest;
use App\Models\Simulator;
use App\Models\SimulatorSession;
use App\Services\ProgressLogService;
use App\Services\SimulatorService;
use App\Services\Simulators\BankSimulator\ClientGeneratorService;
use App\Services\Simulators\BankSimulator\ScoringService;
use App\Services\Simulators\BankSimulator\CreditCalculatorService;
use App\Services\Simulators\BankSimulator\DepositCalculatorService;
use App\Services\Simulators\BankSimulator\DialogueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SimulatorsController extends Controller
{
    public function __construct(
        private SimulatorService $simulatorService,
        private ProgressLogService $progressLogService,
        private ClientGeneratorService $clientGeneratorService,
        private ScoringService $scoringService,
        private CreditCalculatorService $creditCalculatorService,
        private DepositCalculatorService $depositCalculatorService,
        private DialogueService $dialogueService
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

        // Получить историю сессий студента (активные и завершенные)
        $sessions = $this->simulatorService->getStudentSessions($user);

        return Inertia::render('Client/Student/Simulators/Index', [
            'simulators' => $simulators,
            'activeSessions' => $sessions['active'],
            'completedSessions' => $sessions['completed'],
        ]);
    }

    /**
     * Запуск симулятора (создание сессии)
     */
    public function start(Simulator $simulator): RedirectResponse
    {
        $user = auth()->user();

        // Проверить верификацию email
        if (! $user->email_verified_at) {
            return redirect()
                ->route('verification.notice')
                ->with('error', 'Для запуска симулятора необходимо подтвердить ваш email адрес.');
        }

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
    public function session(SimulatorSession $session): Response|RedirectResponse
    {
        $this->authorize('view', $session);

        // Загрузить симулятор и данные сессии
        $session->load(['simulator']);

        // Проверить, что симулятор загружен
        $simulator = $session->simulator;
        if (!$simulator) {
            return redirect()
                ->route('student.simulators.index')
                ->with('error', 'Симулятор не найден');
        }

        // Для банковского симулятора используем специальную страницу
        // Проверяем по slug (из сидера: 'bankovskaya-set-optimizaciya-filialov')
        if ($simulator->slug === 'bankovskaya-set-optimizaciya-filialov') {
            return Inertia::render('Client/Student/Simulators/BankSimulatorSession', [
                'session' => $session,
                'simulator' => $simulator,
            ]);
        }

        return Inertia::render('Client/Student/Simulators/Session', [
            'session' => $session,
            'simulator' => $simulator,
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

    /**
     * Generate client for simulator session
     */
    public function generateClient(Request $request, SimulatorSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $type = $request->input('type', 'random');
        $clientData = $this->clientGeneratorService->generateClient($type);

        // Update session state with client data
        $state = $session->state ?? [];
        $state['client'] = $clientData;
        $session->update(['state' => $state]);

        return response()->json($clientData);
    }

    /**
     * Calculate scoring for client data
     */
    public function calculateScoring(Request $request, SimulatorSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate([
            'income' => 'required|numeric|min:0',
            'expenses' => 'required|numeric|min:0',
            'age' => 'required|integer|min:18|max:100',
            'credit_history' => 'required|string|in:excellent,good,fair,poor,none',
            'has_deposit' => 'boolean',
        ]);

        $clientData = [
            'income' => (float) $request->input('income'),
            'expenses' => (float) $request->input('expenses'),
            'age' => (int) $request->input('age'),
            'credit_history' => $request->input('credit_history'),
            'has_deposit' => (bool) $request->input('has_deposit', false),
        ];

        try {
            $score = $this->scoringService->calculateCreditScore($clientData);
            $interpretation = $this->scoringService->interpretScore($score);

            // Calculate credit limit based on income and multiplier
            $baseLimit = $clientData['income'] * 10; // Base limit is 10x monthly income
            $creditLimit = (int) ($baseLimit * $interpretation['limit_multiplier']);

            $result = [
                'credit_score' => $score,
                'decision' => $interpretation['decision'],
                'interest_rate' => $interpretation['interest_rate'],
                'credit_limit' => $creditLimit,
            ];

            // Update session state with calculations
            $state = $session->state ?? [];
            $state['calculations'] = $result;
            $session->update(['state' => $state]);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Calculate credit payment
     */
    public function calculateCredit(Request $request, SimulatorSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'months' => 'required|integer|min:1|max:120',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        $amount = (float) $request->input('amount');
        $months = (int) $request->input('months');
        $rate = (float) $request->input('rate');

        $monthlyPayment = $this->creditCalculatorService->calculateAnnuityPayment($amount, $months, $rate);
        $totalPayment = $this->creditCalculatorService->calculateTotalPayment($monthlyPayment, $months);
        $overpayment = $this->creditCalculatorService->calculateOverpayment($totalPayment, $amount);

        return response()->json([
            'monthly_payment' => round($monthlyPayment, 2),
            'total_payment' => round($totalPayment, 2),
            'overpayment' => round($overpayment, 2),
        ]);
    }

    /**
     * Calculate deposit result
     */
    public function calculateDeposit(Request $request, SimulatorSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate([
            'initial_amount' => 'required|numeric|min:1000',
            'annual_rate' => 'required|numeric|min:0|max:100',
            'years' => 'required|integer|min:1|max:10',
            'capitalization_periods' => 'integer|min:1|max:365',
        ]);

        $initialAmount = (float) $request->input('initial_amount');
        $annualRate = (float) $request->input('annual_rate');
        $years = (int) $request->input('years');
        $capitalizationPeriods = (int) $request->input('capitalization_periods', 12);

        $finalAmount = $this->depositCalculatorService->calculateDeposit(
            $initialAmount,
            $annualRate,
            $years,
            $capitalizationPeriods
        );
        $income = $finalAmount - $initialAmount;

        return response()->json([
            'final_amount' => round($finalAmount, 2),
            'income' => round($income, 2),
        ]);
    }

    /**
     * Обновить состояние сессии
     */
    public function updateState(UpdateStateRequest $request, SimulatorSession $session): JsonResponse
    {
        $this->authorize('update', $session);

        $this->simulatorService->updateSessionState(
            $session,
            $request->validated()['state']
        );

        return response()->json(['success' => true]);
    }

    /**
     * Получить текущее состояние сессии
     */
    public function getState(SimulatorSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        return response()->json([
            'state' => $session->state ?? [],
        ]);
    }

    /**
     * Process dialogue actions
     */
    public function processDialogueActions(Request $request, SimulatorSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate([
            'stage_id' => 'required|string',
            'option_id' => 'nullable|string',
            'context' => 'nullable|array',
        ]);

        $stageId = $request->input('stage_id');
        $optionId = $request->input('option_id');
        $context = $request->input('context', []);

        // Merge session state into context
        $sessionState = $session->state ?? [];
        $context = array_merge($sessionState, $context);

        $result = [
            'success' => true,
            'effects' => [],
            'updates' => [],
            'messages' => [],
            'next_stage' => null,
        ];

        try {
            // Process option actions if option_id is provided
            if ($optionId !== null) {
                $optionActions = $this->dialogueService->getOptionActions($stageId, $optionId, $context);
                if (!empty($optionActions)) {
                    $actionResult = $this->dialogueService->processActions($stageId, $optionActions, $context);
                    $result['effects'] = array_merge($result['effects'], $actionResult['effects'] ?? []);
                    $result['messages'] = array_merge($result['messages'], $actionResult['messages'] ?? []);
                    if (isset($actionResult['updates'])) {
                        $result['updates'] = array_merge_recursive($result['updates'], $actionResult['updates']);
                    }
                    if (!$actionResult['success']) {
                        $result['success'] = false;
                    }
                }

                // Get next stage
                $result['next_stage'] = $this->dialogueService->processUserChoice($optionId, $stageId, $context);
            }

            // Process stage enter actions for next stage (if transitioning)
            if ($result['next_stage'] !== null) {
                $nextStageId = $result['next_stage'];
                $enterActions = $this->dialogueService->getStageEnterActions($nextStageId, $context);
                if (!empty($enterActions)) {
                    $actionResult = $this->dialogueService->processActions($nextStageId, $enterActions, $context);
                    $result['effects'] = array_merge($result['effects'], $actionResult['effects'] ?? []);
                    $result['messages'] = array_merge($result['messages'], $actionResult['messages'] ?? []);
                    if (isset($actionResult['updates'])) {
                        $result['updates'] = array_merge_recursive($result['updates'], $actionResult['updates']);
                    }
                    if (!$actionResult['success']) {
                        $result['success'] = false;
                    }
                }
            }

            // Update session state if there are updates
            if (!empty($result['updates'])) {
                $this->simulatorService->updateSessionState($session, $result['updates']);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get dialogue stage configuration
     */
    public function getDialogueStage(Request $request, SimulatorSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate([
            'stage_id' => 'required|string',
        ]);

        $stageId = $request->input('stage_id');
        $context = $request->input('context', []);

        try {
            $stageConfig = $this->dialogueService->getStage($stageId, $context);
            return response()->json([
                'success' => true,
                'stage' => $stageConfig,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
