<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Simulator\StoreRequest;
use App\Http\Requests\Admin\Simulator\UpdateRequest;
use App\Models\Simulator;
use App\Models\User;
use App\Services\SimulatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SimulatorController extends Controller
{
    public function __construct(
        private SimulatorService $simulatorService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Simulator::class);

        $filters = $request->only(['search', 'status', 'perPage', 'per_page', 'page']);

        // Нормализуем perPage -> per_page для совместимости с FilterHelper
        if (isset($filters['perPage'])) {
            $filters['per_page'] = $filters['perPage'];
            unset($filters['perPage']);
        }

        $simulators = $this->simulatorService->getFilteredSimulators($filters);

        // Получить список партнеров для формы
        $partners = User::role('partner')->with('partnerProfile')->get()->map(function ($partnerUser) {
            return [
                'id' => $partnerUser->id,
                'name' => $partnerUser->partnerProfile?->company_name ?? $partnerUser->name,
                'contact_person' => $partnerUser->partnerProfile?->contact_person ?? $partnerUser->name ?? 'Без контакта',
            ];
        });

        // Возвращаем filters с perPage для фронтенда
        $frontendFilters = [
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'perPage' => $filters['per_page'] ?? 25,
        ];

        return Inertia::render('Admin/Simulators/Index', [
            'simulators' => $simulators,
            'filters' => $frontendFilters,
            'partners' => $partners,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Simulator::class);

        $data = $request->validated();
        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $request->file('preview_image');
        }

        $this->simulatorService->createSimulator($data);

        return redirect()->route('admin.simulators.index')
            ->with('success', 'Симулятор успешно создан.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Simulator $simulator): RedirectResponse
    {
        $this->authorize('update', $simulator);

        $data = $request->validated();
        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $request->file('preview_image');
        }

        $this->simulatorService->updateSimulator($simulator, $data);

        return redirect()->route('admin.simulators.index')
            ->with('success', 'Симулятор успешно обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Simulator $simulator): RedirectResponse
    {
        $this->authorize('delete', $simulator);

        try {
            $this->simulatorService->deleteSimulator($simulator);

            return redirect()->route('admin.simulators.index')
                ->with('success', 'Симулятор успешно удален.');
        } catch (\Exception $e) {
            return redirect()->route('admin.simulators.index')
                ->with('error', $e->getMessage());
        }
    }
}
