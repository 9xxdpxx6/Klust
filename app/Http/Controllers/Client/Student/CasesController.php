<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Filters\CaseFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Case\AddTeamMemberRequest;
use App\Http\Requests\Student\Case\ApplyRequest;
use App\Models\CaseApplication;
use App\Models\CaseModel;
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

        $caseFilter = new CaseFilter($filters);

        // Базовый запрос: только активные кейсы
        $casesQuery = CaseModel::query()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            })
            ->whereNotExists(function ($query) use ($user): void {
                $query->selectRaw(1)
                    ->from('case_applications')
                    ->whereColumn('case_applications.case_id', 'cases.id')
                    ->where('case_applications.leader_id', $user->id);
            })
            ->with(['partner', 'skills']);

        $pagination = $caseFilter->getPaginationParams();

        $cases = $caseFilter
            ->apply($casesQuery)
            ->paginate($pagination['per_page'])
            ->withQueryString();

        return Inertia::render('Client/Student/Cases/Index', [
            'cases' => $cases,
            'filters' => $filters,
        ]);
    }

    /**
     * Детали кейса
     */
    public function show(CaseModel $case): Response
    {
        $user = auth()->user();

        // Проверить, что кейс активен
        if ($case->status !== 'active') {
            abort(404);
        }

        // Загрузить связи с партнером и его профилем
        $case->load([
            'partner' => function ($query) {
                $query->with(['user.partnerProfile']);
            },
            'skills'
        ]);

        // Получить статус заявки студента
        $applicationStatus = $this->applicationService->getStudentApplicationStatus($user, $case);

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
        // Проверить права
        $this->authorize('addTeamMember', $application);

        // Добавить участника команды
        $teamMember = $this->applicationService->addTeamMember($application, $request->user_id);

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
        $application->load(['status', 'case', 'leader', 'teamMembers.user']);

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

        return Inertia::render('Client/Student/Cases/Team', [
            'team' => $application,
            'progress' => $progress,
        ]);
    }
}
