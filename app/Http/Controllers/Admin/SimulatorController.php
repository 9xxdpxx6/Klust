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

        $simulators = Simulator::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Simulators/Index', [
            'simulators' => $simulators,
            'filters' => $request->only(['search', 'status']),
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