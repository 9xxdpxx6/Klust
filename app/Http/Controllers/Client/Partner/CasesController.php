<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Case\StoreRequest;
use App\Http\Requests\Partner\Case\UpdateRequest;
use App\Http\Requests\Partner\Application\ApproveRequest;
use App\Http\Requests\Partner\Application\RejectRequest;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\Skill;
use App\Services\CaseService;
use App\Services\ApplicationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CasesController extends Controller
{
    public function __construct(
        private CaseService $caseService,
        private ApplicationService $applicationService,
        private NotificationService $notificationService
    ) {
        $this->middleware(['auth', 'role:partner']);
    }

    /**
     * Мои кейсы партнера
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Получить фильтры
        $filters = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        // Получить только кейсы партнера
        $cases = $this->caseService->getPartnerCases($partner, $filters);

        // Применить пагинацию
        $cases = $cases->paginate(15);

        return Inertia::render('Client/Partner/Cases/Index', [
            'cases' => $cases,
            'filters' => $filters,
        ]);
    }

    /**
     * Форма создания кейса
     */
    public function create(): Response
    {
        // Получить список всех навыков
        $skills = Skill::all();

        // Получить список симуляторов партнера (опционально)
        $user = auth()->user();
        $simulators = $user->partner?->simulators ?? collect();

        return Inertia::render('Client/Partner/Cases/Create', [
            'skills' => $skills,
            'simulators' => $simulators,
        ]);
    }

    /**
     * Сохранение кейса
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        if (!$partner) {
            return redirect()
                ->route('partner.cases.index')
                ->with('error', 'Партнер не найден');
        }

        // Создать кейс
        $case = $this->caseService->createCaseForPartner($partner, $request->validated());

        // Если статус 'active', отправить уведомления студентам
        if ($case->status === 'active') {
            $this->notificationService->notifyStudentsAboutNewCase($case);
        }

        return redirect()
            ->route('partner.cases.show', $case)
            ->with('success', 'Кейс успешно создан');
    }

    /**
     * Детали кейса
     */
    public function show(CaseModel $case): Response
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права (только свой кейс)
        $this->caseService->ensureCaseBelongsToPartner($case, $partner);

        // Загрузить связи
        $case->load(['skills', 'applications.leader', 'applications.teamMembers.user']);

        // Получить статистику
        $statistics = $this->caseService->getCaseStatistics($case);

        // Получить команды
        $teams = $case->applications()
            ->where('status', 'accepted')
            ->with(['leader', 'teamMembers.user'])
            ->get();

        return Inertia::render('Client/Partner/Cases/Show', [
            'case' => $case,
            'applications' => $case->applications,
            'teams' => $teams,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Форма редактирования кейса
     */
    public function edit(CaseModel $case): Response
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права
        $this->caseService->ensureCaseBelongsToPartner($case, $partner);

        // Загрузить кейс со связанными навыками
        $case->load('skills');

        // Получить список навыков
        $skills = Skill::all();

        return Inertia::render('Client/Partner/Cases/Edit', [
            'case' => $case,
            'skills' => $skills,
        ]);
    }

    /**
     * Обновление кейса
     */
    public function update(UpdateRequest $request, CaseModel $case): RedirectResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права
        $this->caseService->ensureCaseBelongsToPartner($case, $partner);

        // Обновить кейс
        $this->caseService->updateCase($case, $request->validated());

        return redirect()
            ->route('partner.cases.show', $case)
            ->with('success', 'Кейс успешно обновлен');
    }

    /**
     * Архивация кейса
     */
    public function archive(CaseModel $case): RedirectResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права
        $this->caseService->ensureCaseBelongsToPartner($case, $partner);

        // Изменить статус на 'archived'
        $this->caseService->archiveCase($case);

        return redirect()
            ->route('partner.cases.index')
            ->with('success', 'Кейс успешно архивирован');
    }

    /**
     * Управление заявками
     */
    public function applications(CaseModel $case, Request $request): Response
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права
        $this->caseService->ensureCaseBelongsToPartner($case, $partner);

        // Получить заявки на кейс с фильтрацией по статусу
        $statusFilter = $request->input('status');
        $applications = $case->applications()
            ->when($statusFilter, function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->with(['leader', 'teamMembers.user'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        return Inertia::render('Client/Partner/Cases/Applications', [
            'case' => $case,
            'applications' => $applications,
        ]);
    }

    /**
     * Одобрение заявки
     */
    public function approve(ApproveRequest $request, CaseModel $case, CaseApplication $application): RedirectResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права (кейс принадлежит партнеру)
        $this->caseService->ensureCaseBelongsToPartner($case, $partner);

        // Проверить, что заявка имеет статус 'pending'
        if ($application->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Заявка уже обработана');
        }

        // Одобрить заявку
        $this->applicationService->approveApplication($application, $request->comment ?? null);

        // Отправить уведомления команде
        $this->notificationService->notifyTeamAboutApproval($application);

        return redirect()
            ->back()
            ->with('success', 'Заявка успешно одобрена');
    }

    /**
     * Отклонение заявки
     */
    public function reject(RejectRequest $request, CaseModel $case, CaseApplication $application): RedirectResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права
        $this->caseService->ensureCaseBelongsToPartner($case, $partner);

        // Проверить статус заявки
        if ($application->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Заявка уже обработана');
        }

        // Отклонить заявку
        $this->applicationService->rejectApplication($application, $request->rejection_reason);

        // Отправить уведомление лидеру команды
        $this->notificationService->notifyApplicationRejection($application);

        return redirect()
            ->back()
            ->with('success', 'Заявка отклонена');
    }
}

