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

        // Загрузить связи
        $case->load(['partner', 'skills']);

        // Получить статус заявки студента
        $applicationStatus = $this->applicationService->getStudentApplicationStatus($user, $case);

        return Inertia::render('Client/Student/Cases/Show', [
            'case' => $case,
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

        // Создать заявку
        $application = $this->applicationService->createApplication(
            $user,
            $case,
            $request->validated()
        );

        // Отправить уведомление партнеру
        $this->notificationService->notifyPartnerAboutApplication($application);

        return redirect()
            ->route('student.cases.show', $case)
            ->with('success', 'Заявка успешно подана');
    }

    /**
     * Добавление участника команды
     */
    public function addTeamMember(AddTeamMemberRequest $request, CaseApplication $application): RedirectResponse
    {
        $user = auth()->user();

        // Проверить, что заявка принадлежит студенту и имеет статус 'pending'
        if ($application->leader_id !== $user->id || $application->status !== 'pending') {
            abort(403);
        }

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
        $user = auth()->user();

        // Проверить права (только лидер заявки может отозвать)
        if ($application->leader_id !== $user->id) {
            abort(403);
        }

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
        $user = auth()->user();

        // Проверить, что заявка принята
        if ($application->status !== 'accepted') {
            abort(404);
        }

        // Проверить, что студент входит в команду
        $isTeamMember = $application->leader_id === $user->id ||
            $application->teamMembers()->where('user_id', $user->id)->exists();

        if (! $isTeamMember) {
            abort(403);
        }

        // Загрузить команду со всеми участниками
        $application->load(['leader', 'teamMembers.user', 'case']);

        // Получить прогресс команды
        $progress = $this->teamService->getTeamProgress($application);

        return Inertia::render('Client/Student/Cases/Team', [
            'team' => $application,
            'progress' => $progress,
        ]);
    }
}
