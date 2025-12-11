<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Case\StoreRequest;
use App\Http\Requests\Admin\Case\UpdateRequest;
use App\Models\CaseModel;
use App\Models\Partner;
use App\Models\Simulator;
use App\Models\Skill;
use App\Services\CaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaseController extends Controller
{
    public function __construct(
        private CaseService $caseService
    ) {
        $this->middleware(['auth', 'role:admin|teacher']);
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CaseModel::class);

        $user = auth()->user();

        // Получить фильтры (status, partner_id, search, perPage)
        $filters = [
            'status' => $request->input('status'),
            'partner_id' => $request->input('partner_id'),
            'search' => $request->input('search'),
            'per_page' => $request->input('perPage') ?? $request->input('per_page'),
        ];

        // Если пользователь - партнер, показывать только его кейсы
        if ($user->hasRole('partner') && $user->partner?->id) {
            $filters['partner_id'] = $user->partner->id;
        }

        // Получить кейсы через CaseService::getFilteredCases($filters)
        $cases = $this->caseService->getFilteredCases($filters);

        // Получаем список партнеров для фильтра
        $partners = Partner::with('user.partnerProfile')
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'company_name' => $partner->company_name ?? 'Без названия',
                    'contact_person' => $partner->contact_person ?? 'Без контакта',
                ];
            });

        // Возвращаем filters с perPage для фронтенда
        $frontendFilters = [
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'partner_id' => $filters['partner_id'] ?? '',
            'team_size' => $request->input('team_size', ''),
            'perPage' => $filters['per_page'] ?? 25,
        ];

        // Получаем общую статистику
        // Если пользователь - партнер, показывать статистику только по его кейсам
        $statisticsQuery = CaseModel::query();
        if ($user->hasRole('partner') && $user->partner?->id) {
            $statisticsQuery->where('partner_id', $user->partner->id);
        }
        
        $statistics = [
            'total_cases' => $statisticsQuery->count(),
            'active_cases' => (clone $statisticsQuery)->where('status', 'active')->count(),
            'draft_cases' => (clone $statisticsQuery)->where('status', 'draft')->count(),
            'completed_cases' => (clone $statisticsQuery)->where('status', 'completed')->count(),
            'archived_cases' => (clone $statisticsQuery)->where('status', 'archived')->count(),
        ];

        return Inertia::render('Admin/Cases/Index', [
            'cases' => $cases,
            'filters' => $frontendFilters,
            'partners' => $partners,
            'statistics' => $statistics,
        ]);
    }

    public function show(CaseModel $case): Response
    {
        $this->authorize('view', $case);

        // Загрузить связи: partner, skills, applications, teams
        $case->load([
            'partner.user.partnerProfile',
            'simulator',
            'skills',
            'applications.leader.studentProfile.faculty',
            'applications.status',
            'applications.teamMembers.user.studentProfile.faculty',
        ]);

        // Получить статистику через CaseService::getCaseStatistics($case)
        $statistics = $this->caseService->getCaseStatistics($case);

        // Группировать заявки по статусам
        $applicationsByStatus = [
            'pending' => $case->applications->filter(function ($app) {
                return $app->status && $app->status->name === 'pending';
            })->values(),
            'approved' => $case->applications->filter(function ($app) {
                return $app->status && $app->status->name === 'accepted';
            })->values(),
            'rejected' => $case->applications->filter(function ($app) {
                return $app->status && $app->status->name === 'rejected';
            })->values(),
        ];

        // Проверка связанных данных, которые могут помешать удалению
        $activeApplicationsCount = $case->applications()
            ->whereHas('status', function ($q) {
                $q->whereIn('name', ['pending', 'accepted']);
            })
            ->count();

        $hasBlockingData = $activeApplicationsCount > 0;

        $blockingData = [
            'active_applications_count' => $activeApplicationsCount,
            'has_blocking_data' => $hasBlockingData,
        ];

        return Inertia::render('Admin/Cases/Show', [
            'caseData' => $case,
            'statistics' => $statistics,
            'applicationsByStatus' => $applicationsByStatus,
            'blockingData' => $blockingData,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', CaseModel::class);

        // Получить список партнеров и симуляторов
        $partners = Partner::with('user')->get()->map(function ($partner) {
            return [
                'id' => $partner->id,
                'name' => $partner->name ?? 'Без названия',
                'contact_person' => $partner->user->name ?? 'Без контакта',
            ];
        });

        // Получить список всех навыков
        $skills = Skill::all()->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
            ];
        });

        $statusOptions = [
            ['label' => 'Черновик', 'value' => 'draft'],
            ['label' => 'Активный', 'value' => 'active'],
            ['label' => 'Завершенный', 'value' => 'completed'],
            ['label' => 'Архивный', 'value' => 'archived'],
        ];

        return Inertia::render('Admin/Cases/Create', [
            'partners' => $partners,
            'skills' => $skills,
            'simulators' => Simulator::all(),
            'statusOptions' => $statusOptions,
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->authorize('create', CaseModel::class);

        try {
            // Использовать CaseService::createCase($request->validated())
            // Создать кейс и привязать навыки через sync
            $case = $this->caseService->createCase($request->validated());

            return redirect()
                ->route('admin.cases.show', $case)
                ->with('success', 'Кейс успешно создан');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ошибка при создании кейса: '.$e->getMessage());
        }
    }

    public function edit(CaseModel $case): Response
    {
        $this->authorize('update', $case);

        // Загрузить кейс со связанными навыками
        $case->load('skills');

        // Получить список партнеров и симуляторов
        $partners = Partner::with('user')->get()->map(function ($partner) {
            return [
                'id' => $partner->id,
                'name' => $partner->name ?? 'Без названия',
                'contact_person' => $partner->user->name ?? 'Без контакта',
            ];
        });

        $skills = Skill::all()->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
            ];
        });

        $statusOptions = [
            ['label' => 'Черновик', 'value' => 'draft'],
            ['label' => 'Активный', 'value' => 'active'],
            ['label' => 'Завершенный', 'value' => 'completed'],
            ['label' => 'Архивный', 'value' => 'archived'],
        ];

        // Подготовить данные кейса с required_skills как массив ID
        $caseData = $case->toArray();
        $caseData['required_skills'] = $case->skills->pluck('id')->toArray();

        return Inertia::render('Admin/Cases/Edit', [
            'case' => $caseData,
            'partners' => $partners,
            'skills' => $skills,
            'simulators' => Simulator::all(),
            'statusOptions' => $statusOptions,
        ]);
    }

    public function update(UpdateRequest $request, CaseModel $case): RedirectResponse
    {
        $this->authorize('update', $case);

        try {
            // Использовать CaseService::updateCase($case, $request->validated())
            // Обновить навыки через sync
            $this->caseService->updateCase($case, $request->validated());

            return redirect()
                ->route('admin.cases.show', $case)
                ->with('success', 'Кейс успешно обновлен');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ошибка при обновлении кейса: '.$e->getMessage());
        }
    }

    public function destroy(CaseModel $case): RedirectResponse
    {
        $this->authorize('delete', $case);

        try {
            // Проверить, нет ли активных заявок
            // Использовать CaseService::deleteCase($case)
            $this->caseService->deleteCase($case);

            return redirect()
                ->route('admin.cases.index')
                ->with('success', 'Кейс успешно удален');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при удалении кейса: '.$e->getMessage());
        }
    }
}
