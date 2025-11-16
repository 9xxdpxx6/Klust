<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Badge\StoreRequest;
use App\Http\Requests\Admin\Badge\UpdateRequest;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BadgeController extends Controller
{
    public function __construct(
        private BadgeService $badgeService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Badge::class);

        $filters = $request->only(['search', 'perPage', 'per_page', 'page']);
        
        // Нормализуем perPage -> per_page для совместимости с FilterHelper
        if (isset($filters['perPage'])) {
            $filters['per_page'] = $filters['perPage'];
            unset($filters['perPage']);
        }

        $badges = $this->badgeService->getFilteredBadges($filters);

        // Возвращаем filters с perPage для фронтенда
        $frontendFilters = [
            'search' => $filters['search'] ?? '',
            'perPage' => $filters['per_page'] ?? 15,
        ];

        return Inertia::render('Admin/Badges/Index', [
            'badges' => $badges,
            'filters' => $frontendFilters,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Badge::class);

        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon');
        }

        $this->badgeService->createBadge($data);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Badge $badge): RedirectResponse
    {
        $this->authorize('update', $badge);

        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon');
        }

        $this->badgeService->updateBadge($badge, $data);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Badge $badge): RedirectResponse
    {
        $this->authorize('delete', $badge);

        try {
            $this->badgeService->deleteBadge($badge);

            return redirect()->route('admin.badges.index')
                ->with('success', 'Badge deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.badges.index')
                ->with('error', $e->getMessage());
        }
    }
}