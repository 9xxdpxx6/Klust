<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Simulator;
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
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('viewAny', Simulator::class);

        $filters = $request->only(['search', 'status', 'perPage', 'per_page', 'page']);
        
        // Нормализуем perPage -> per_page для совместимости с FilterHelper
        if (isset($filters['perPage'])) {
            $filters['per_page'] = $filters['perPage'];
            unset($filters['perPage']);
        }

        $simulators = $this->simulatorService->getFilteredSimulators($filters);

        // Возвращаем filters с perPage для фронтенда
        $frontendFilters = [
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'perPage' => $filters['per_page'] ?? 15,
        ];

        return Inertia::render('Admin/Simulators/Index', [
            'simulators' => $simulators,
            'filters' => $frontendFilters,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('create', Simulator::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'url' => 'required|url|max:500',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $validated;
        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $request->file('preview_image');
        }

        $this->simulatorService->createSimulator($data);

        return redirect()->route('admin.simulators.index')
            ->with('success', 'Simulator created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Simulator $simulator): RedirectResponse
    {
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('update', $simulator);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'url' => 'required|url|max:500',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $validated;
        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $request->file('preview_image');
        }

        $this->simulatorService->updateSimulator($simulator, $data);

        return redirect()->route('admin.simulators.index')
            ->with('success', 'Simulator updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Simulator $simulator): RedirectResponse
    {
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('delete', $simulator);

        try {
            $this->simulatorService->deleteSimulator($simulator);

            return redirect()->route('admin.simulators.index')
                ->with('success', 'Simulator deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.simulators.index')
                ->with('error', $e->getMessage());
        }
    }
}