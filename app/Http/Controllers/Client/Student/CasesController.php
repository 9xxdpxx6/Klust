<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Filters\CaseFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Case\AddTeamMemberRequest;
use App\Http\Requests\Student\Case\ApplyRequest;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\Skill;
use App\Services\ApplicationService;
use App\Services\CaseService;
use App\Services\NotificationService;
use App\Services\TeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CasesController extends Controller
{
    public function __construct(
        private CaseService $caseService,
        private ApplicationService $applicationService,
        private TeamService $teamService,
        private NotificationService $notificationService
    ) {
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Каталог доступных кейсов
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $filters = $request->only([
            'search',
            'skills',
            'partner_id',
            'status',
            'deadline_from',
            'deadline_to',
            'sort_by',
            'sort_order',
            'per_page',
        ]);

        // Ensure skills is an array
        if (isset($filters['skills']) && !is_array($filters['skills'])) {
            $filters['skills'] = is_string($filters['skills']) 
                ? explode(',', $filters['skills']) 
                : [$filters['skills']];
        }

        $caseFilter = new CaseFilter($filters);

        // Базовый запрос: если статус не указан, показываем только активные
        // Если статус указан, CaseFilter применит его
        $casesQuery = CaseModel::query()
            ->where(function ($query): void {
                $query->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            })
            ->with(['partner', 'skills']);

        // Если статус не указан в фильтрах, показываем только активные кейсы
        if (!isset($filters['status']) || $filters['status'] === '') {
            $casesQuery->where('status', 'active');
        }

        $pagination = $caseFilter->getPaginationParams();

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $cases */
        $cases = $caseFilter
            ->apply($casesQuery)
            ->paginate($pagination['per_page'])
            ->appends($request->query());

        // Добавить информацию о заявках студента для каждого кейса
        foreach ($cases->items() as $case) {
            $application = $this->applicationService->getStudentApplicationStatus($user, $case);
            if ($application) {
                // Загрузить статус, если еще не загружен
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

        // Получаем список партнеров для фильтра
        $partners = \App\Models\User::role('partner')->with('partnerProfile')
            ->get()
            ->map(function ($partnerUser) {
                return [
                    'id' => $partnerUser->id,
                    'name' => $partnerUser->partnerProfile?->company_name ?? $partnerUser->name ?? 'Без названия',
                ];
            });

        // Получаем список навыков для фильтра
        $availableSkills = Skill::query()
            ->orderBy('name')
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ];
            });

        // Возвращаем filters с perPage для фронтенда
        $frontendFilters = [
            'search' => $filters['search'] ?? '',
            'skills' => $filters['skills'] ?? [],
            'partner_id' => $filters['partner_id'] ?? '',
            'status' => $filters['status'] ?? '',
            'per_page' => $pagination['per_page'],
        ];

        return Inertia::render('Client/Student/Cases/Index', [
            'cases' => $cases,
            'filters' => $frontendFilters,
            'partners' => $partners,
            'availableSkills' => $availableSkills,
            'recommended' => false,
        ]);
    }

    /**
     * Рекомендованные кейсы (подбор по навыкам студента)
     */
    public function recommended(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $filters = $request->only([
            'search',
            'skills',
            'partner_id',
            'deadline_from',
            'deadline_to',
            'sort_by',
            'sort_order',
            'per_page',
        ]);

        // Ensure skills is an array
        if (isset($filters['skills']) && !is_array($filters['skills'])) {
            $filters['skills'] = is_string($filters['skills'])
                ? explode(',', $filters['skills'])
                : [$filters['skills']];
        }

        // Фиксируем статус: рекомендации только по активным (не даем фильтром сбить выборку)
        unset($filters['status']);

        $caseFilter = new CaseFilter($filters);

        $studentSkillIds = $user->skills()->pluck('skills.id')->toArray();
        $appliedCaseIds = $this->caseService->getAppliedCaseIdsForUser($user);

        $casesQuery = CaseModel::query()
            ->where('status', 'active')
            ->whereNotIn('id', $appliedCaseIds)
            ->where(function ($query): void {
                $query->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            })
            ->with(['partner', 'skills']);

        if (!empty($studentSkillIds)) {
            $casesQuery
                ->whereHas('skills', function ($q) use ($studentSkillIds) {
                    $q->whereIn('skills.id', $studentSkillIds);
                })
                ->withCount([
                    'skills as matching_skills_count' => function ($q) use ($studentSkillIds) {
                        $q->whereIn('skills.id', $studentSkillIds);
                    },
                ]);
        }

        $pagination = $caseFilter->getPaginationParams();

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $cases */
        $cases = $caseFilter
            ->apply($casesQuery)
            ->reorder()
            ->when(!empty($studentSkillIds), function ($q) {
                $q->orderByDesc('matching_skills_count');
            })
            ->orderByDesc('id')
            ->paginate($pagination['per_page'])
            ->appends($request->query());

        // Добавить информацию о заявках студента для каждого кейса
        foreach ($cases->items() as $case) {
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

        $partners = \App\Models\User::role('partner')->with('partnerProfile')
            ->get()
            ->map(function ($partnerUser) {
                return [
                    'id' => $partnerUser->id,
                    'name' => $partnerUser->partnerProfile?->company_name ?? $partnerUser->name ?? 'Без названия',
                ];
            });

        $availableSkills = Skill::query()
            ->orderBy('name')
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ];
            });

        $frontendFilters = [
            'search' => $filters['search'] ?? '',
            'skills' => $filters['skills'] ?? [],
            'partner_id' => $filters['partner_id'] ?? '',
            'status' => '',
            'per_page' => $pagination['per_page'],
        ];

        return Inertia::render('Client/Student/Cases/Index', [
            'cases' => $cases,
            'filters' => $frontendFilters,
            'partners' => $partners,
            'availableSkills' => $availableSkills,
            'recommended' => true,
        ]);
    }

    /**
     * Детали кейса
     */
    public function show(CaseModel $case): Response
    {
        $user = auth()->user();

        // Получить статус заявки студента
        $applicationStatus = $this->applicationService->getStudentApplicationStatus($user, $case);

        // Если кейс не активен, проверяем, есть ли у студента принятая заявка
        if ($case->status !== 'active') {
            // Разрешаем просмотр только если у студента есть принятая заявка на этот кейс
            if (!$applicationStatus) {
                abort(404);
            }

            $acceptedStatusId = \App\Models\ApplicationStatus::getIdByName('accepted');
            if ($applicationStatus->status_id !== $acceptedStatusId) {
                abort(404);
            }
        }

        // Загрузить связи с партнером и его профилем
        $case->load([
            'partner' => function ($query) {
                $query->with(['user.partnerProfile']);
            },
            'skills'
        ]);

        // Загрузить историю статусов, если заявка существует
        if ($applicationStatus) {
            $applicationStatus->load([
                'status',
                'statusHistory.changedBy',
                'statusHistory.oldStatus',
                'statusHistory.newStatus',
            ]);
        }

        // Убеждаемся, что partner загружен с нужными связями
        if ($case->partner) {
            // Принудительно загружаем связи, если они еще не загружены
            if (!$case->partner->relationLoaded('user')) {
                $case->partner->load('user.partnerProfile');
            }
        }

        return Inertia::render('Client/Student/Cases/Show', [
            'caseData' => $case,
            'applicationStatus' => $applicationStatus,
        ]);
    }

    /**
     * Мои кейсы
     */
    public function myCases(): Response
    {
        $user = auth()->user();

        // Получить кейсы по категориям
        $groupedCases = $this->applicationService->getStudentCasesGrouped($user);

        return Inertia::render('Client/Student/Cases/MyCases', [
            'cases' => $groupedCases,
        ]);
    }

    /**
     * Подача заявки на кейс
     */
    public function apply(ApplyRequest $request, CaseModel $case): RedirectResponse
    {
        $user = auth()->user();

        // Проверить, что студент не подал уже заявку
        if ($this->applicationService->hasApplication($user, $case)) {
            return redirect()
                ->route('student.cases.show', $case)
                ->with('error', 'Вы уже подали заявку на этот кейс');
        }

        // Проверить, что кейс активен и дедлайн не прошел
        if ($case->status !== 'active' || ($case->deadline && $case->deadline->isPast())) {
            return redirect()
                ->route('student.cases.show', $case)
                ->with('error', 'Кейс недоступен для подачи заявки');
        }

        // Подготовить данные для создания заявки
        $data = $request->validated();
        
        // Если переданы email'ы, конвертируем их в user_id
        if ($request->has('team_member_emails') && is_array($request->team_member_emails)) {
            $userIds = \App\Models\User::whereIn('email', $request->team_member_emails)
                ->where('id', '!=', $user->id) // Исключаем текущего пользователя
                ->pluck('id')
                ->toArray();
            
            $data['team_members'] = $userIds;
        }

        try {
            // Создать заявку
            $application = $this->applicationService->createApplication(
                $user,
                $case,
                $data
            );

            // Отправить уведомление партнеру (асинхронно, не блокирует ответ)
            try {
                $this->notificationService->notifyPartnerAboutApplication($application);
            } catch (\Exception $e) {
                Log::error('Failed to notify partner about application', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
                // Не прерываем выполнение, если уведомление не отправилось
            }

            return redirect()
                ->route('student.cases.show', $case)
                ->with('success', 'Заявка успешно подана');
        } catch (\Exception $e) {
            Log::error('Failed to create application', [
                'user_id' => $user->id,
                'case_id' => $case->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()
                ->route('student.cases.show', $case)
                ->with('error', 'Произошла ошибка при подаче заявки: '.$e->getMessage());
        }
    }

    /**
     * Добавление участника команды
     */
    public function addTeamMember(AddTeamMemberRequest $request, CaseApplication $application): RedirectResponse
    {
        // Нельзя менять состав команды после одобрения заявки
        $pendingStatusId = \App\Models\ApplicationStatus::getIdByName('pending');
        if ($application->status_id !== $pendingStatusId) {
            return redirect()
                ->back()
                ->with('error', 'Нельзя добавлять/удалять участников после одобрения заявки');
        }

        // Проверить права
        $this->authorize('addTeamMember', $application);

        try {
            // Добавить участника команды
            $teamMember = $this->applicationService->addTeamMember($application, $request->user_id);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        // Отправить уведомление новому участнику
        $this->notificationService->notifyUserAboutTeamAddition(
            $teamMember->user,
            $application
        );

        return redirect()
            ->route('student.cases.my')
            ->with('success', 'Участник команды успешно добавлен');
    }

    /**
     * Отзыв заявки
     */
    public function withdraw(CaseApplication $application): RedirectResponse
    {
        // Проверить права (только лидер заявки может отозвать)
        $this->authorize('delete', $application);

        // Отозвать заявку
        $this->applicationService->withdrawApplication($application);

        return redirect()
            ->route('student.cases.my')
            ->with('success', 'Заявка успешно отозвана');
    }

    /**
     * Страница команды
     */
    public function team(CaseApplication $application): Response
    {
        // Загрузить все необходимые связи ДО проверок
        $application->load([
            'status',
            'case.skills',
            'case.partner',
            'case.partnerUser.partnerProfile',
            'leader',
            'teamMembers.user',
        ]);

        // Получить ID статуса "accepted"
        $acceptedStatusId = \App\Models\ApplicationStatus::getIdByName('accepted');

        // Проверить, что заявка принята (используем status_id для избежания N+1)
        if ($application->status_id !== $acceptedStatusId) {
            abort(404);
        }

        // Проверить права доступа к команде (Policy может безопасно использовать загруженные связи)
        $this->authorize('viewTeam', $application);

        // Получить прогресс команды
        $progress = $this->teamService->getTeamProgress($application);
        $activity = $this->teamService->getTeamActivityHistory($application);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $isLeader = (int) $application->leader_id === (int) $user->id;

        return Inertia::render('Client/Student/Cases/Team', [
            // Current expected props in Team.vue
            'application' => $application,
            'teamProgress' => $progress,
            'teamActivity' => $activity,
            'isLeader' => $isLeader,

            // Backward compatibility (older builds/components)
            'team' => $application,
            'progress' => $progress,
        ]);
    }
}
