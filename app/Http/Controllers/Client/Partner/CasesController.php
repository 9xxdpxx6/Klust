<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Exports\ApplicationsExport;
use App\Filters\CaseApplicationFilter;
use App\Filters\CaseFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Application\ApproveRequest;
use App\Http\Requests\Partner\Application\RejectRequest;
use App\Http\Requests\Partner\Application\UpdateStatusRequest;
use App\Http\Requests\Partner\Case\StoreRequest;
use App\Http\Requests\Partner\Case\UpdateRequest;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\Skill;
use App\Services\ApplicationService;
use App\Services\CaseService;
use App\Services\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $this->middleware(['auth', 'role:partner|admin|teacher']);
    }

    /**
     * Мои кейсы партнера
     */
    public function index(Request $request): Response|RedirectResponse
    {
        try {
            $user = auth()->user();

            // Если админ или преподаватель, можно просматривать кейсы любого партнера
            // Если партнер - только свои кейсы
            $partnerId = null;
            if ($user->hasAnyRole(['admin', 'teacher'])) {
                $partnerId = $request->input('partner_id');
                // Если partner_id не указан, редиректим на админскую страницу кейсов
                if (!$partnerId) {
                    return redirect()->route('admin.cases.index');
                }
                // Проверяем, что указанный пользователь действительно партнер
                $partnerUser = \App\Models\User::find($partnerId);
                if (!$partnerUser || !$partnerUser->hasRole('partner')) {
                    return redirect()->route('admin.cases.index')
                        ->with('error', 'Партнер не найден');
                }
            } elseif ($user->hasRole('partner')) {
                $partnerId = $user->id;
            } else {
                return Inertia::render('Client/Partner/Cases/Index', [
                    'cases' => [],
                    'filters' => [],
                    'error' => 'Доступ запрещен',
                ]);
            }

            $filters = $request->only([
                'status',
                'search',
                'simulator_id',
                'deadline_from',
                'deadline_to',
                'date_from',
                'date_to',
                'sort_by',
                'sort_order',
                'per_page',
            ]);

            // Явно исключаем partner_id из фильтров, чтобы предотвратить перезапись
            unset($filters['partner_id']);

            $caseFilter = new CaseFilter($filters);

            // Создаем запрос с жестким условием по user_id (partner_id)
            $casesQuery = CaseModel::query()
                ->where('user_id', $partnerId)
                ->with(['skills', 'simulator']);

            $pagination = $caseFilter->getPaginationParams();

            // Применяем фильтр, но partner_id уже установлен и не может быть перезаписан
            $cases = $caseFilter
                ->apply($casesQuery)
                ->paginate($pagination['per_page'])
                ->appends($request->only(['partner_id']))
                ->withQueryString();

            // Получаем статистику для табов
            $baseQuery = CaseModel::query()->where('user_id', $partnerId);
            $statistics = [
                'draft_count' => (clone $baseQuery)->where('status', 'draft')->count(),
                'active_count' => (clone $baseQuery)->where('status', 'active')->count(),
                'completed_count' => (clone $baseQuery)->where('status', 'completed')->count(),
                'archived_count' => (clone $baseQuery)->where('status', 'archived')->count(),
            ];

            return Inertia::render('Client/Partner/Cases/Index', [
                'cases' => $cases,
                'filters' => $filters,
                'statistics' => $statistics,
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
    public function create(): Response|RedirectResponse
    {
        $user = auth()->user();

        // Проверить верификацию email
        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('error', 'Для создания кейса необходимо подтвердить ваш email адрес.');
        }

        try {
            // Получить список всех навыков
            $skills = Skill::all();

            // Получить список симуляторов партнера (опционально)
            $simulators = \App\Models\Simulator::where('user_id', $user->id)->get();

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

            // Проверить верификацию email
            if (! $user->hasVerifiedEmail()) {
                return redirect()
                    ->route('verification.notice')
                    ->with('error', 'Для создания кейса необходимо подтвердить ваш email адрес.');
            }

            if (! $user->hasRole('partner')) {
                return redirect()
                    ->route('partner.cases.index')
                    ->with('error', 'Партнер не найден');
            }

            // Создать кейс
            $case = $this->caseService->createCaseForPartner($user, $request->validated());

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
    public function show(CaseModel $case, Request $request): Response
    {
        try {
            $user = auth()->user();

            // Проверить права (только свой кейс)
            $this->authorize('view', $case);

            // Загрузить основные связи
            $case->load(['skills']);

            // Получить статистику
            $statistics = $this->caseService->getCaseStatistics($case);

            // Получить команды с навыками
            $acceptedStatusId = \App\Models\ApplicationStatus::getIdByName('accepted');
            $teams = $case->applications()
                ->where('status_id', $acceptedStatusId)
                ->with(['leader.skills', 'teamMembers.user.skills'])
                ->get()
                ->map(function ($application) use ($case) {
                    // Собираем всех участников команды (лидер + участники)
                    $allMembers = collect([$application->leader])
                        ->merge($application->teamMembers->pluck('user'))
                        ->filter();
                    
                    // Собираем все навыки команды (уникальные ID)
                    $teamSkillIds = $allMembers
                        ->flatMap(fn($member) => $member->skills->pluck('id'))
                        ->unique()
                        ->values()
                        ->toArray();
                    
                    // Получаем навыки кейса
                    $caseSkillIds = $case->skills->pluck('id')->toArray();
                    
                    // Вычисляем покрытые и непокрытые навыки
                    $coveredSkillIds = array_intersect($caseSkillIds, $teamSkillIds);
                    $missingSkillIds = array_diff($caseSkillIds, $teamSkillIds);
                    
                    // Добавляем информацию о навыках в объект команды через setAttribute
                    $application->setAttribute('team_skill_ids', $teamSkillIds);
                    $application->setAttribute('covered_skill_ids', array_values($coveredSkillIds));
                    $application->setAttribute('missing_skill_ids', array_values($missingSkillIds));
                    
                    return $application;
                });

            // Пагинация заявок с фильтрами
            $applicationsQuery = \App\Models\CaseApplication::query()
                ->where('case_id', $case->id)
                ->with(['leader.skills', 'status', 'teamMembers.user.skills', 'statusHistory.changedBy', 'statusHistory.oldStatus', 'statusHistory.newStatus']);

            // Применить фильтры через CaseApplicationFilter
            $filter = new \App\Filters\CaseApplicationFilter([
                'search' => $request->query('search'),
                'status' => $request->query('status'),
                'sort_by' => $request->query('sort_by', 'id'),
                'sort_order' => $request->query('sort_order', 'desc'),
            ]);
            
            $applicationsQuery = $filter->apply($applicationsQuery);

            $applications = $applicationsQuery
                ->paginate(10)
                ->withQueryString();
            
            // Добавляем информацию о навыках для каждой заявки
            $applications->getCollection()->transform(function ($application) use ($case) {
                // Собираем ID всех участников команды (лидер + участники)
                $memberIds = collect();
                
                // Добавляем ID лидера
                if ($application->leader_id) {
                    $memberIds->push($application->leader_id);
                }
                
                // Добавляем ID участников команды
                if ($application->teamMembers) {
                    foreach ($application->teamMembers as $teamMember) {
                        if ($teamMember->user_id) {
                            $memberIds->push($teamMember->user_id);
                        }
                    }
                }
                
                // Убираем дубликаты
                $memberIds = $memberIds->unique()->values();
                
                // Получаем все навыки команды напрямую из БД
                $teamSkillIds = DB::table('user_skills')
                    ->whereIn('user_id', $memberIds->toArray())
                    ->distinct()
                    ->pluck('skill_id')
                    ->toArray();
                
                // Получаем навыки кейса
                $caseSkillIds = $case->skills ? $case->skills->pluck('id')->toArray() : [];
                
                // Вычисляем покрытые и непокрытые навыки
                $coveredSkillIds = array_intersect($caseSkillIds, $teamSkillIds);
                $missingSkillIds = array_diff($caseSkillIds, $teamSkillIds);
                
                // Добавляем информацию о навыках в объект заявки через setAttribute
                $application->setAttribute('team_skill_ids', $teamSkillIds);
                $application->setAttribute('covered_skill_ids', array_values($coveredSkillIds));
                $application->setAttribute('missing_skill_ids', array_values($missingSkillIds));
                
                return $application;
            });


            return Inertia::render('Client/Partner/Cases/Show', [
                'caseData' => $case,
                'applications' => $applications,
                'teams' => $teams,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            // Возвращаем пустую пагинированную структуру при ошибке
            $emptyPagination = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                10,
                1
            );

            return Inertia::render('Client/Partner/Cases/Show', [
                'caseData' => $case,
                'applications' => $emptyPagination,
                'teams' => collect(),
                'statistics' => [
                    'total_applications' => 0,
                    'pending_applications' => 0,
                    'accepted_applications' => 0,
                    'rejected_applications' => 0,
                ],
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

            if (! $user->hasRole('partner')) {
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
        } catch (AuthorizationException $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при архивации кейса: У вас нет прав для выполнения этого действия.');
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

            // Use direct query to CaseApplication with case_id filter
            $applicationsQuery = \App\Models\CaseApplication::query()
                ->where('case_id', $case->id)
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
        $startTime = microtime(true);
        
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
            $serviceStart = microtime(true);
            $updatedApplication = $this->applicationService->approveApplication($application, $request->comment ?? null);
            $serviceTime = round((microtime(true) - $serviceStart) * 1000, 2);
            
            // Перезагрузить кейс с актуальными данными
            $case->refresh();

            // Отправить уведомления команде об изменении статуса (async)
            $notificationStart = microtime(true);
            $this->notificationService->notifyTeamAboutStatusChange(
                $updatedApplication,
                'pending',
                'accepted',
                $request->comment ?? null
            );
            $notificationTime = round((microtime(true) - $notificationStart) * 1000, 2);
            
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Application status changed: approved', [
                'application_id' => $updatedApplication->id,
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'service_time_ms' => $serviceTime,
                'notification_time_ms' => $notificationTime,
                'total_time_ms' => $totalTime,
            ]);

            return redirect()
                ->route('partner.cases.show', $case)
                ->with('success', 'Заявка успешно одобрена');
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Error approving application', [
                'application_id' => $application->id,
                'case_id' => $case->id,
                'error' => $e->getMessage(),
                'total_time_ms' => $totalTime,
            ]);
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
        $startTime = microtime(true);
        
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
            $serviceStart = microtime(true);
            $updatedApplication = $this->applicationService->rejectApplication($application, $request->rejection_reason);
            $serviceTime = round((microtime(true) - $serviceStart) * 1000, 2);

            // Перезагрузить кейс с актуальными данными
            $case->refresh();

            // Отправить уведомления команде об изменении статуса (async)
            // Обернуто в try-catch чтобы ошибки уведомлений не ломали основной поток
            $notificationStart = microtime(true);
            try {
                $this->notificationService->notifyTeamAboutStatusChange(
                    $updatedApplication,
                    'pending',
                    'rejected',
                    $request->rejection_reason
                );
            } catch (\Exception $notificationError) {
                // Логируем ошибку уведомления, но не прерываем процесс
                Log::warning('Failed to send rejection notification, but application was rejected', [
                    'application_id' => $updatedApplication->id,
                    'error' => $notificationError->getMessage(),
                ]);
            }
            $notificationTime = round((microtime(true) - $notificationStart) * 1000, 2);
            
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Application status changed: rejected', [
                'application_id' => $updatedApplication->id,
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'service_time_ms' => $serviceTime,
                'notification_time_ms' => $notificationTime,
                'total_time_ms' => $totalTime,
            ]);
            
            return redirect()
                ->route('partner.cases.show', $case->id)
                ->with('success', 'Заявка отклонена');
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Error rejecting application', [
                'application_id' => $application->id,
                'case_id' => $case->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'total_time_ms' => $totalTime,
                'exception_class' => get_class($e),
            ]);
            
            // Редиректим на страницу кейса вместо back(), чтобы избежать проблем с GET запросами
            return redirect()
                ->route('partner.cases.show', $case->id)
                ->with('error', 'Ошибка при отклонении заявки: '.$e->getMessage());
        }
    }

    /**
     * Изменение статуса заявки
     */
    public function updateApplicationStatus(UpdateStatusRequest $request, CaseModel $case, CaseApplication $application): RedirectResponse
    {
        $startTime = microtime(true);
        
        try {
            // Проверить права (кейс принадлежит партнеру и дедлайн не прошел)
            $this->authorize('updateApplicationStatus', $case);

            $newStatus = $request->validated()['status'];
            
            // Загружаем статус если еще не загружен
            if (!$application->relationLoaded('status')) {
                $application->load('status');
            }
            $oldStatus = $application->status->name ?? 'unknown';

            // Изменить статус заявки
            $serviceStart = microtime(true);
            $updatedApplication = $this->applicationService->updateApplicationStatus(
                $application,
                $newStatus,
                $request->comment ?? null
            );
            $serviceTime = round((microtime(true) - $serviceStart) * 1000, 2);

            // Перезагрузить кейс с актуальными данными
            $case->refresh();

            // Отправить уведомления команде при любом изменении статуса (async)
            $notificationTime = 0;
            if ($oldStatus !== $newStatus) {
                $notificationStart = microtime(true);
                $this->notificationService->notifyTeamAboutStatusChange(
                    $updatedApplication,
                    $oldStatus,
                    $newStatus,
                    $request->comment ?? null
                );
                $notificationTime = round((microtime(true) - $notificationStart) * 1000, 2);
            }

            $totalTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Application status changed', [
                'application_id' => $updatedApplication->id,
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'service_time_ms' => $serviceTime,
                'notification_time_ms' => $notificationTime,
                'total_time_ms' => $totalTime,
            ]);

            return redirect()
                ->route('partner.cases.show', $case)
                ->with('success', "Статус заявки изменен на: " . ($updatedApplication->status->label ?? $newStatus));
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Error updating application status', [
                'application_id' => $application->id,
                'case_id' => $case->id,
                'error' => $e->getMessage(),
                'total_time_ms' => $totalTime,
            ]);
            return redirect()
                ->back()
                ->with('error', 'Ошибка при изменении статуса заявки: '.$e->getMessage());
        }
    }

    /**
     * Экспорт заявок на кейс в Excel
     */
    public function exportApplications(CaseModel $case, Request $request): BinaryFileResponse
    {
        // Проверить права
        $this->authorize('viewApplications', $case);

        $filters = [
            'case_id' => $case->id,
        ];

        // Добавить фильтр по статусу, если передан
        if ($request->has('status') && $request->get('status') !== '' && $request->get('status') !== null) {
            $filters['status'] = $request->get('status');
        }

        $filename = 'applications_case_'.$case->id.'_'.date('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new ApplicationsExport($filters), $filename);
    }
}
