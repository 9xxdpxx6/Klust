<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\CaseSkill;
use App\Models\Partner;
use App\Models\Simulator;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Cases\StoreCaseRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Partner\Cases\UpdateCaseRequest;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check()) {
            logger('Current user: ' . auth()->id());
            logger('User roles: ' . implode(', ', auth()->user()->getRoleNames()->toArray()));
        } else {
            logger('No authenticated user');
        }

        // Получаем параметры фильтрации из запроса
        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', ''),
            'partner_id' => $request->input('partner_id', ''),
            'team_size' => $request->input('team_size', ''),
            'perPage' => $request->input('perPage', 15),
        ];

        // Строим запрос
        $query = CaseModel::with(['partner.user'])
            ->withCount('applications');

        // Поиск по названию или описанию
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Фильтрация по статусу
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Фильтрация по партнеру
        if (!empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }

        // Фильтрация по размеру команды
        if (!empty($filters['team_size'])) {
            $teamSize = (int)$filters['team_size'];
            if ($teamSize === 5) {
                $query->where('required_team_size', '>=', 5);
            } else {
                $query->where('required_team_size', $teamSize);
            }
        }

        // Сортировка и пагинация
        $cases = $query->orderBy('created_at', 'desc')
            ->paginate($filters['perPage'])
            ->withQueryString();

        // Получаем список партнеров для фильтра
        $partners = Partner::with('user')
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'company_name' => $partner->company_name,
                    'contact_person' => $partner->user->name,
                ];
            });

        return Inertia::render('Admin/Cases/Index', [
            'cases' => $cases,
            'filters' => $filters,
            'partners' => $partners,
        ]);
    }

    public function show(CaseModel $case)
    {
        // Загружаем кейс со всеми связями
        $case->load([
            'partner.user',
            'simulator',
            'skills',
            'applications' => function ($query) {
                $query->with([
                    'user.studentProfile',
                    'teamMembers.user.studentProfile'
                ])->orderBy('created_at', 'desc');
            },
            'applications.teamMembers.user.studentProfile'
        ]);

        // Статистика по кейсу
        $statistics = [
            'total_applications' => $case->applications_count,
            'pending_applications' => $case->applications->where('status', 'pending')->count(),
            'approved_applications' => $case->applications->where('status', 'approved')->count(),
            'rejected_applications' => $case->applications->where('status', 'rejected')->count(),
            'average_team_size' => $case->applications->avg('team_members_count') + 1, // +1 для лидера
        ];

        // Группируем заявки по статусу
        $applicationsByStatus = [
            'pending' => $case->applications->where('status', 'pending'),
            'approved' => $case->applications->where('status', 'approved'),
            'rejected' => $case->applications->where('status', 'rejected'),
        ];

        return Inertia::render('Admin/Cases/Show', [
            'case' => $case,
            'statistics' => $statistics,
            'applicationsByStatus' => $applicationsByStatus,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Cases/Create', [
            'partners' => Partner::with('user')->get()->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'company_name' => $partner->company_name,
                    'contact_person' => $partner->user->name ?? 'Без контакта',
                ];
            }),
            'skills' => Skill::all()->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'category' => $skill->category,
                ];
            }),
            'simulators' => Simulator::all(), // если есть симуляторы
            'statusOptions' => [
                ['value' => 'draft', 'label' => 'Черновик'],
                ['value' => 'active', 'label' => 'Активный'],
            ],
        ]);
    }

    public function store(StoreCaseRequest $request)
    {
        try {
            DB::beginTransaction();

            // Создаем кейс согласно валидации
            $case = CaseModel::create([
                'title' => $request->title,
                'description' => $request->description,
                'partner_id' => $request->partner_id, // из формы
                'simulator_id' => $request->simulator_id,
                'deadline' => $request->deadline,
                'reward' => $request->reward,
                'required_team_size' => $request->required_team_size,
                'status' => $request->status ?? 'draft', // по умолчанию черновик
            ]);

            // Прикрепляем навыки - используем required_skills из валидации
            if ($request->has('required_skills') && is_array($request->required_skills)) {
                foreach ($request->required_skills as $skillId) {
                    CaseSkill::create([
                        'case_id' => $case->id,
                        'skill_id' => $skillId,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.cases.index')
                ->with('success', 'Кейс успешно создан!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Ошибка при создании кейса: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(CaseModel $case)
    {
        return Inertia::render('Admin/Cases/Edit', [
            'case' => [
                'id' => $case->id,
                'title' => $case->title,
                'description' => $case->description,
                'partner_id' => $case->partner_id,
                'deadline' => $case->deadline->format('Y-m-d'),
                'reward' => $case->reward,
                'required_team_size' => $case->required_team_size,
                'status' => $case->status,
                'simulator_id' => $case->simulator_id,
                'required_skills' => $case->skills->pluck('id'),
            ],
            'partners' => Partner::with('user')->get()->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'company_name' => $partner->company_name,
                    'contact_person' => $partner->user->name ?? 'Без контакта',
                ];
            }),
            'skills' => Skill::all()->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'category' => $skill->category,
                ];
            }),
            'statusOptions' => [
                ['value' => 'draft', 'label' => 'Черновик'],
                ['value' => 'active', 'label' => 'Активный'],
                ['value' => 'completed', 'label' => 'Завершенный'],
                ['value' => 'archived', 'label' => 'Архивный'],
            ],
        ]);
    }

    public function update(UpdateCaseRequest $request, CaseModel $case)
    {
        try {
            DB::beginTransaction();

            // Обновляем кейс - данные уже валидированы в UpdateCaseRequest
            $case->update([
                'title' => $request->title,
                'description' => $request->description,
                'partner_id' => $request->partner_id,
                'deadline' => $request->deadline,
                'reward' => $request->reward,
                'required_team_size' => $request->required_team_size,
                'status' => $request->status,
                'simulator_id' => $request->simulator_id,
            ]);

            // Обновляем навыки если они переданы
            if ($request->has('required_skills')) {
                // Удаляем старые навыки
                $case->skills()->detach();

                // Добавляем новые навыки
                if (is_array($request->required_skills) && !empty($request->required_skills)) {
                    foreach ($request->required_skills as $skillId) {
                        CaseSkill::create([
                            'case_id' => $case->id,
                            'skill_id' => $skillId,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.cases.index')
                ->with('success', 'Кейс успешно обновлен!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Ошибка при обновлении кейса: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(CaseModel $case)
    {
        try {
            DB::beginTransaction();

            // Удаляем связанные записи
            $case->skills()->detach(); // Удаляем связи с навыками

            // Если есть другие связи, добавьте их удаление здесь
            // Например: $case->applications()->delete();

            // Удаляем сам кейс
            $case->delete();

            DB::commit();

            return redirect()->route('admin.cases.index')
                ->with('success', 'Кейс успешно удален!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Ошибка при удалении кейса: ' . $e->getMessage());
        }
    }
}
