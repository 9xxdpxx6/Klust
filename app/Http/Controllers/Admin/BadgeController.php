<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('viewAny', Badge::class);

        $badges = Badge::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('required_points')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Badges/Index', [
            'badges' => $badges,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('create', Badge::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'required_points' => 'required|integer|min:0',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $validated;
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
    public function update(Request $request, Badge $badge): RedirectResponse
    {
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('update', $badge);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'required_points' => 'required|integer|min:0',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $validated;
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
        // TODO: Создать Policy и раскомментировать
        // $this->authorize('delete', $badge);

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