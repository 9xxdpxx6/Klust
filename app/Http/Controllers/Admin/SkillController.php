<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Skill\StoreRequest;
use App\Http\Requests\Admin\Skill\UpdateRequest;
use App\Models\Skill;
use App\Services\SkillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function __construct(
        private SkillService $skillService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Skill::class);

        $filters = $request->only(['search', 'category', 'perPage', 'per_page', 'page']);

        // Нормализуем perPage -> per_page для совместимости с FilterHelper
        if (isset($filters['perPage'])) {
            $filters['per_page'] = $filters['perPage'];
            unset($filters['perPage']);
        }

        $skills = $this->skillService->getFilteredSkills($filters);

        // Возвращаем filters с perPage для фронтенда
        $frontendFilters = [
            'search' => $filters['search'] ?? '',
            'category' => $filters['category'] ?? '',
            'perPage' => $filters['per_page'] ?? 25,
        ];

        return Inertia::render('Admin/Skills/Index', [
            'skills' => $skills,
            'filters' => $frontendFilters,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Skill::class);

        $this->skillService->createSkill($request->validated());

        return redirect()->route('admin.skills.index')
            ->with('success', 'Навык успешно создан.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Skill $skill): RedirectResponse
    {
        $this->authorize('update', $skill);

        $this->skillService->updateSkill($skill, $request->validated());

        return redirect()->route('admin.skills.index')
            ->with('success', 'Навык успешно обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill): RedirectResponse
    {
        $this->authorize('delete', $skill);

        try {
            $this->skillService->deleteSkill($skill);

            return redirect()->route('admin.skills.index')
                ->with('success', 'Навык успешно удален.');
        } catch (\Exception $e) {
            return redirect()->route('admin.skills.index')
                ->with('error', $e->getMessage());
        }
    }
}
