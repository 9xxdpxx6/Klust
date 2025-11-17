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
        // Получить фильтры (status, partner_id, search)
        $filters = [
            'status' => $request->input('status'),
            'partner_id' => $request->input('partner_id'),
            'search' => $request->input('search'),
        ];

        // Получить кейсы через CaseService::getFilteredCases($filters)
        $cases = $this->caseService->getFilteredCases($filters);

        // Получаем список партнеров для фильтра
        $partners = Partner::with('user')
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'name' => $partner->name ?? 'Без названия',
                    'contact_person' => $partner->user->name ?? 'Без контакта',
                ];
            });

        return Inertia::render('Admin/Cases/Index', [
            'cases' => $cases,
            'filters' => $filters,
            'partners' => $partners,
        ]);
    }

    public function show(CaseModel $case): Response
    {
        // Загрузить связи: partner, skills, applications, teams
        $case->load([
            'partner.user',
            'simulator',
            'skills',
            'applications.leader',
            'applications.status',
            'applications.teamMembers.user',
        ]);

        // Получить статистику через CaseService::getCaseStatistics($case)
        $statistics = $this->caseService->getCaseStatistics($case);

        return Inertia::render('Admin/Cases/Show', [
            'caseData' => $case,
            'statistics' => $statistics,
        ]);
    }

    public function create(): Response
    {
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
