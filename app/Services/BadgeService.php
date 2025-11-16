<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    public function __construct(
        private FileService $fileService
    ) {}

    /**
     * Create new badge
     */
    public function createBadge(array $data): Badge
    {
        $iconPath = null;

        // Handle icon upload
        if (isset($data['icon'])) {
            $iconPath = $this->fileService->storeBadgeIcon($data['icon']);
        }

        return Badge::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon' => $iconPath,
            'required_points' => $data['required_points'] ?? 0,
        ]);
    }

    /**
     * Update badge
     */
    public function updateBadge(Badge $badge, array $data): Badge
    {
        // Handle icon update
        if (isset($data['icon'])) {
            // Delete old icon if exists
            if ($badge->icon) {
                $this->fileService->deleteFile($badge->icon);
            }

            $badge->icon = $this->fileService->storeBadgeIcon($data['icon']);
        }

        $badge->update([
            'name' => $data['name'] ?? $badge->name,
            'description' => $data['description'] ?? $badge->description,
            'icon' => $badge->icon,
            'required_points' => $data['required_points'] ?? $badge->required_points,
        ]);

        return $badge->fresh();
    }

    /**
     * Delete badge
     */
    public function deleteBadge(Badge $badge): bool
    {
        return DB::transaction(function () use ($badge) {
            // Delete icon file if exists
            if ($badge->icon) {
                $this->fileService->deleteFile($badge->icon);
            }

            // Detach from users
            $badge->users()->detach();

            return $badge->delete();
        });
    }

    /**
     * Get student badges
     */
    public function getStudentBadges(User $user): Collection
    {
        return $user->badges()
            ->withPivot('earned_at')
            ->orderByDesc('pivot_earned_at')
            ->get()
            ->map(function ($badge) {
                // If icon starts with 'pi-', it's a PrimeIcon class, not a file path
                $iconPath = null;
                if ($badge->icon && !str_starts_with($badge->icon, 'pi-')) {
                    $iconPath = '/storage/' . $badge->icon;
                }

                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'icon_path' => $iconPath,
                    'required_points' => $badge->required_points,
                    'earned_at' => $badge->pivot->earned_at,
                ];
            });
    }

    /**
     * Check and award badges based on student points
     *
     * @return array Array of newly awarded badges
     */
    public function checkBadgeEligibility(User $user): array
    {
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            return [];
        }

        $totalPoints = $studentProfile->total_points;

        // Get badges student doesn't have yet
        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();

        $eligibleBadges = Badge::where('required_points', '<=', $totalPoints)
            ->whereNotIn('id', $earnedBadgeIds)
            ->get();

        $newBadges = [];

        foreach ($eligibleBadges as $badge) {
            // Award badge to student
            $user->badges()->attach($badge->id, [
                'earned_at' => now(),
            ]);

            $newBadges[] = $badge;
        }

        return $newBadges;
    }

    /**
     * Get filtered badges with pagination
     */
    public function getFilteredBadges(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Badge::query();

        // Apply search filter
        $search = \App\Helpers\FilterHelper::getStringFilter($filters['search'] ?? null);
        if ($search) {
            $sanitizedSearch = \App\Helpers\FilterHelper::sanitizeSearch($search);
            $query->where('name', 'like', "%{$sanitizedSearch}%");
        }

        // Get pagination parameters
        $pagination = \App\Helpers\FilterHelper::getPaginationParams($filters, 15);

        return $query->orderBy('required_points')
            ->orderBy('name')
            ->paginate($pagination['per_page'])
            ->withQueryString();
    }

    /**
     * Get upcoming badges for student (not yet earned)
     */
    public function getUpcomingBadges(User $user, int $limit = 5): Collection
    {
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            return collect();
        }

        $totalPoints = $studentProfile->total_points;
        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();

        return Badge::where('required_points', '>', $totalPoints)
            ->whereNotIn('id', $earnedBadgeIds)
            ->orderBy('required_points')
            ->limit($limit)
            ->get()
            ->map(function ($badge) use ($totalPoints) {
                // If icon starts with 'pi-', it's a PrimeIcon class, not a file path
                $iconPath = null;
                if ($badge->icon && !str_starts_with($badge->icon, 'pi-')) {
                    $iconPath = '/storage/' . $badge->icon;
                }

                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'icon_path' => $iconPath,
                    'required_points' => $badge->required_points,
                    'points_needed' => $badge->required_points - $totalPoints,
                    'progress_percentage' => round(($totalPoints / $badge->required_points) * 100, 2),
                ];
            });
    }
}
