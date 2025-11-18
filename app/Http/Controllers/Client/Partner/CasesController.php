<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Exports\ApplicationsExport;
use App\Filters\CaseApplicationFilter;
use App\Filters\CaseFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Application\ApproveRequest;
use App\Http\Requests\Partner\Application\RejectRequest;
use App\Http\Requests\Partner\Case\StoreRequest;
use App\Http\Requests\Partner\Case\UpdateRequest;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\Skill;
use App\Services\ApplicationService;
use App\Services\CaseService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        try {
            $user = auth()->user();
            $partner = $user->partner;

            if (! $partner) {
                return Inertia::render('Client/Partner/Cases/Index', [
                    'cases' => [],
                    'filters' => [],
                    'error' => 'Партнер не найден',
                ]);
            }

            $filters = $request->only([
                'status',
                'search',
                'simulator_id',
                'deadline_from',
                'deadline_to',
                'sort_by',
                'sort_order',
                'per_page',
            ]);

            $caseFilter = new CaseFilter($filters);

            $casesQuery = CaseModel::query()
                ->where('partner_id', $partner->id)
                ->with(['skills', 'simulator']);

            $pagination = $caseFilter->getPaginationParams();

            $cases = $caseFilter
                ->apply($casesQuery)
                ->paginate($pagination['per_page'])
                ->withQueryString();

            return Inertia::render('Client/Partner/Cases/Index', [
                'cases' => $cases,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Cases/Index', [
                'cases' => [],
                'filters' => [],
                'error' => 'Ошибка при загрузке кейсов: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Форма создания кейса
     */
    public function create(): Response
    {
        try {
            // Получить список всех навыков
            $skills = Skill::all();

            // Получить список симуляторов партнера (опционально)
            $user = auth()->user();
            $simulators = $user->partner?->simulators ?? collect();

            return Inertia::render('Client/Partner/Cases/Create', [
                'skills' => $skills,
                'simulators' => $simulators,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Cases/Create', [
                'skills' => [],
                'simulators' => collect(),
                'error' => 'Ошибка при загрузке формы: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Сохранение кейса
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();
            $partner = $user->partner;

            if (! $partner) {
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
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ошибка при создании кейса: '.$e->getMessage());
        }
    }

    /**
     * Детали кейса
     */
    public function show(CaseModel $case): Response
    {
        try {
            $user = auth()->user();
            $partner = $user->partner;

            // Проверить права (только свой кейс)
            $this->authorize('view', $case);

            // Загрузить связи
            $case->load([
                'skills',
                'applications.leader',
                'applications.status',
                'applications.teamMembers.user',
                'applications.statusHistory.changedBy',
                'applications.statusHistory.oldStatus',
                'applications.statusHistory.newStatus',
            ]);

            // Получить статистику
            $statistics = $this->caseService->getCaseStatistics($case);

            // Получить команды
            $acceptedStatusId = \App\Models\ApplicationStatus::getIdByName('accepted');
            $teams = $case->applications()
                ->where('status_id', $acceptedStatusId)
                ->with(['leader', 'teamMembers.user'])
                ->get();

            return Inertia::render('Client/Partner/Cases/Show', [
                'caseData' => $case,
                'applications' => $case->applications,
                'teams' => $teams,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Cases/Show', [
                'caseData' => $case,
                'applications' => collect(),
                'teams' => collect(),
                'statistics' => [],
                'error' => 'Ошибка при загрузке кейса: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Форма редактирования кейса
     */
    public function edit(CaseModel $case): Response
    {
        try {
            $user = auth()->user();
            $partner = $user->partner;

            if (! $partner) {
                return Inertia::render('Client/Partner/Cases/Applications', [
                    'case' => $case,
                    'applications' => collect(),
                    'filters' => [],
                    'error' => 'Партнер не найден',
                ]);
            }

            // Проверить права
            $this->authorize('update', $case);

            // Загрузить кейс со связанными навыками
            $case->load('skills');

            // Получить список навыков
            $skills = Skill::all();

            return Inertia::render('Client/Partner/Cases/Edit', [
                'caseData' => $case,
                'skills' => $skills,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Cases/Edit', [
                'caseData' => $case,
                'skills' => [],
                'error' => 'Ошибка при загрузке формы: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Обновление кейса
     */
    public function update(UpdateRequest $request, CaseModel $case): RedirectResponse
    {
        try {
            // Проверить права
            $this->authorize('update', $case);

            // Обновить кейс
            $this->caseService->updateCase($case, $request->validated());

            return redirect()
                ->route('partner.cases.show', $case)
                ->with('success', 'Кейс успешно обновлен');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ошибка при обновлении кейса: '.$e->getMessage());
        }
    }

    /**
     * Архивация кейса
     */
    public function archive(CaseModel $case): RedirectResponse
    {
        try {
            // Проверить права
            $this->authorize('archive', $case);

            // Изменить статус на 'archived'
            $this->caseService->archiveCase($case);

            return redirect()
                ->route('partner.cases.index')
                ->with('success', 'Кейс успешно архивирован');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при архивации кейса: '.$e->getMessage());
        }
    }

    /**
     * Управление заявками
     */
    public function applications(CaseModel $case, Request $request): Response
    {
        try {
            // Проверить права
            $this->authorize('viewApplications', $case);

            $filters = $request->only([
                'search',
                'status',
                'case_id',
                'submitted_from',
                'submitted_to',
                'sort_by',
                'sort_order',
                'per_page',
            ]);

            $applicationFilter = new CaseApplicationFilter($filters);

            $applicationsQuery = $case->applications()
                ->with([
                    'leader',
                    'status',
                    'teamMembers.user',
                    'statusHistory.changedBy',
                    'statusHistory.oldStatus',
                    'statusHistory.newStatus',
                ]);

            $pagination = $applicationFilter->getPaginationParams();

            $applications = $applicationFilter
                ->apply($applicationsQuery)
                ->paginate($pagination['per_page'])
                ->withQueryString();

            return Inertia::render('Client/Partner/Cases/Applications', [
                'case' => $case,
                'applications' => $applications,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Client/Partner/Cases/Applications', [
                'case' => $case,
                'applications' => collect(),
                'error' => 'Ошибка при загрузке заявок: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Одобрение заявки
     */
    public function approve(ApproveRequest $request, CaseModel $case, CaseApplication $application): RedirectResponse
    {
        try {
            // Проверить права (кейс принадлежит партнеру)
            $this->authorize('approveApplication', $case);

            // Проверить, что заявка имеет статус 'pending'
            $pendingStatusId = \App\Models\ApplicationStatus::getIdByName('pending');
            if ($application->status_id !== $pendingStatusId) {
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
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при одобрении заявки: '.$e->getMessage());
        }
    }

    /**
     * Отклонение заявки
     */
    public function reject(RejectRequest $request, CaseModel $case, CaseApplication $application): RedirectResponse
    {
        try {
            // Проверить права
            $this->authorize('rejectApplication', $case);

            // Проверить статус заявки
            $pendingStatusId = \App\Models\ApplicationStatus::getIdByName('pending');
            if ($application->status_id !== $pendingStatusId) {
                return redirect()
                    ->back()
                    ->with('error', 'Заявка уже обработана');
            }

            // Отклонить заявку
            $this->applicationService->rejectApplication($application, $request->rejection_reason);

            // Отправить уведомление лидеру команды
            $this->notificationService->notifyApplicationRejection($application, $request->rejection_reason);

            return redirect()
                ->back()
                ->with('success', 'Заявка отклонена');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при отклонении заявки: '.$e->getMessage());
        }
    }

    /**
     * Экспорт заявок на кейс в Excel
     */
    public function exportApplications(CaseModel $case): BinaryFileResponse
    {
        // Проверить права
        $this->authorize('viewApplications', $case);

        $filename = 'applications_case_'.$case->id.'_'.date('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new ApplicationsExport(['case_id' => $case->id]), $filename);
    }
}
