<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Simulator\CompleteRequest;
use App\Models\Simulator;
use App\Models\SimulatorSession;
use App\Services\ProgressLogService;
use App\Services\SimulatorService;
use App\Services\Simulators\BankSimulator\ClientGeneratorService;
use App\Services\Simulators\BankSimulator\ScoringService;
use App\Services\Simulators\BankSimulator\CreditCalculatorService;
use App\Services\Simulators\BankSimulator\DepositCalculatorService;
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
        private DepositCalculatorService $depositCalculatorService
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
        if (! $user->hasVerifiedEmail()) {
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
    public function session(SimulatorSession $session): Response
    {
        $this->authorize('view', $session);

        // Загрузить симулятор и данные сессии
        $session->load(['simulator']);

        // Для банковского симулятора используем специальную страницу
        $simulator = $session->simulator;
        if ($simulator && $simulator->slug === 'bank-simulator') {
            return Inertia::render('Client/Student/Simulators/BankSimulatorSession', [
                'session' => $session,
                'simulator' => $simulator,
            ]);
        }

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
}
