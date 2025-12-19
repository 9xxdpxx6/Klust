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
        return Badge::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'required_points' => $data['required_points'] ?? 0,
            'points_increment' => $data['points_increment'] ?? 0,
            'max_level' => $data['max_level'] ?? null,
        ]);
    }

    /**
     * Update badge
     */
    public function updateBadge(Badge $badge, array $data): Badge
    {
        // Handle icon update - delete old file if it was a file path
        if (isset($data['icon']) && $badge->icon && ! $badge->isPrimeIcon()) {
            // Old icon was a file, delete it
            $this->fileService->deleteFile($badge->icon);
        }

        $badge->update([
            'name' => $data['name'] ?? $badge->name,
            'description' => $data['description'] ?? $badge->description,
            'icon' => $data['icon'] ?? $badge->icon,
            'required_points' => $data['required_points'] ?? $badge->required_points,
            'points_increment' => $data['points_increment'] ?? $badge->points_increment,
            'max_level' => $data['max_level'] ?? $badge->max_level,
        ]);

        return $badge->fresh();
    }

    /**
     * Delete badge
     */
    public function deleteBadge(Badge $badge): bool
    {
        // Check if badge is assigned to users
        $usedByUsers = $badge->users()->count();
        if ($usedByUsers > 0) {
            throw new \Exception("Cannot delete badge that is assigned to {$usedByUsers} user(s)");
        }

        return DB::transaction(function () use ($badge) {
            // Delete icon file if exists and it's not a PrimeIcon class
            if ($badge->icon && ! $badge->isPrimeIcon()) {
                $this->fileService->deleteFile($badge->icon);
            }

            // Detach from users (should be empty at this point, but just in case)
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
            ->withPivot('earned_at', 'level')
            ->orderByDesc('pivot_earned_at')
            ->get()
            ->map(function ($badge) {
                // If icon starts with 'pi-' or 'fa-', it's an icon class, not a file path
                $iconPath = null;
                if ($badge->icon && ! str_starts_with($badge->icon, 'pi-') && ! str_starts_with($badge->icon, 'fa-')) {
                    $iconPath = '/storage/'.$badge->icon;
                }

                $currentLevel = $badge->pivot->level ?? 1;
                $requiredPointsForCurrentLevel = $badge->getRequiredPointsForLevel($currentLevel);
                $requiredPointsForNextLevel = null;
                $isMaxLevel = false;

                // Check if there's a next level
                if ($badge->max_level === null || $currentLevel < $badge->max_level) {
                    $requiredPointsForNextLevel = $badge->getRequiredPointsForLevel($currentLevel + 1);
                } else {
                    $isMaxLevel = true;
                }

                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'icon_path' => $iconPath,
                    'required_points' => $badge->required_points,
                    'level' => $currentLevel,
                    'max_level' => $badge->max_level,
                    'is_max_level' => $isMaxLevel,
                    'required_points_for_current_level' => $requiredPointsForCurrentLevel,
                    'required_points_for_next_level' => $requiredPointsForNextLevel,
                    'earned_at' => $badge->pivot->earned_at,
                ];
            });
    }

    /**
     * Check and award badges based on student points
     * Supports multiple levels: badge can be earned multiple times with increasing level
     *
     * @return array Array of newly awarded/upgraded badges with level info
     */
    public function checkBadgeEligibility(User $user): array
    {
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            return [];
        }

        $totalPoints = $studentProfile->total_points;

        // Get all badges user currently has with their levels
        $userBadges = $user->badges()
            ->withPivot('level')
            ->get()
            ->keyBy('id');

        // Get all badges in system
        $allBadges = Badge::all();

        $newBadges = [];

        foreach ($allBadges as $badge) {
            $userBadge = $userBadges->get($badge->id);
            
            if ($userBadge === null) {
                // User doesn't have this badge yet - check for level 1
                $requiredPointsForLevel1 = $badge->getRequiredPointsForLevel(1);
                
                if ($totalPoints >= $requiredPointsForLevel1) {
                    // Award badge at level 1
            $user->badges()->attach($badge->id, [
                        'level' => 1,
                'earned_at' => now(),
            ]);

                    $newBadges[] = [
                        'badge' => $badge,
                        'level' => 1,
                        'is_upgrade' => false,
                    ];
                }
            } else {
                // User already has this badge - check for next level
                $currentLevel = $userBadge->pivot->level ?? 1;
                $nextLevel = $currentLevel + 1;
                
                // Check if badge has max level and user reached it
                if ($badge->max_level !== null && $nextLevel > $badge->max_level) {
                    continue; // Already at max level
                }
                
                // Calculate required points for next level
                $requiredPointsForNextLevel = $badge->getRequiredPointsForLevel($nextLevel);
                
                if ($totalPoints >= $requiredPointsForNextLevel) {
                    // Upgrade badge to next level
                    DB::table('user_badges')
                        ->where('user_id', $user->id)
                        ->where('badge_id', $badge->id)
                        ->update([
                            'level' => $nextLevel,
                            'earned_at' => now(), // Update earned_at to reflect latest upgrade
                        ]);
                    
                    $newBadges[] = [
                        'badge' => $badge,
                        'level' => $nextLevel,
                        'is_upgrade' => true,
                    ];
                }
            }
        }

        return $newBadges;
    }

    /**
     * Get filtered badges with pagination
     */
    public function getFilteredBadges(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Badge::query()
            ->withCount('users');

        // Apply search filter
        $search = \App\Helpers\FilterHelper::getStringFilter($filters['search'] ?? null);
        if ($search) {
            $sanitizedSearch = \App\Helpers\FilterHelper::sanitizeSearch($search);
            $query->where('name', 'like', "%{$sanitizedSearch}%");
        }

        // Get pagination parameters
        $pagination = \App\Helpers\FilterHelper::getPaginationParams($filters, 15);

        $badges = $query->orderBy('required_points')
            ->orderBy('name')
            ->paginate($pagination['per_page'])
            ->withQueryString();

        // Transform badges to include icon_path and users_count
        $badges->setCollection(
            $badges->getCollection()->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'icon_path' => $badge->icon_path,
                    'required_points' => $badge->required_points,
                    'points_increment' => $badge->points_increment ?? 0,
                    'max_level' => $badge->max_level,
                    'users_count' => $badge->users_count ?? 0,
                    'created_at' => $badge->created_at,
                    'updated_at' => $badge->updated_at,
                ];
            })
        );

        return $badges;
    }

    /**
     * Get upcoming badges for student (not yet earned or next level)
     */
    public function getUpcomingBadges(User $user, int $limit = 5): Collection
    {
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            return collect();
        }

        $totalPoints = $studentProfile->total_points;
        
        // Get badges user has with their levels
        $userBadges = $user->badges()
            ->withPivot('level')
            ->get()
            ->keyBy('id');

        // Get all badges
        $allBadges = Badge::orderBy('required_points')->get();

        $upcoming = collect();

        foreach ($allBadges as $badge) {
            $userBadge = $userBadges->get($badge->id);
            
            if ($userBadge === null) {
                // User doesn't have this badge - show level 1 requirement
                $requiredPointsForLevel1 = $badge->getRequiredPointsForLevel(1);
                
                if ($requiredPointsForLevel1 > $totalPoints) {
                    $iconPath = null;
                    if ($badge->icon && ! str_starts_with($badge->icon, 'pi-') && ! str_starts_with($badge->icon, 'fa-')) {
                        $iconPath = '/storage/'.$badge->icon;
                    }

                    $upcoming->push([
                        'id' => $badge->id,
                        'name' => $badge->name,
                        'description' => $badge->description,
                        'icon' => $badge->icon,
                        'icon_path' => $iconPath,
                        'required_points' => $requiredPointsForLevel1,
                        'level' => 1,
                        'points_needed' => $requiredPointsForLevel1 - $totalPoints,
                        'progress_percentage' => $requiredPointsForLevel1 > 0 
                            ? round(($totalPoints / $requiredPointsForLevel1) * 100, 2) 
                            : 0,
                    ]);
                }
            } else {
                // User has this badge - check if there's a next level
                $currentLevel = $userBadge->pivot->level ?? 1;
                
                // Check if badge has max level and user reached it
                if ($badge->max_level !== null && $currentLevel >= $badge->max_level) {
                    continue; // Already at max level, skip
                }
                
                $nextLevel = $currentLevel + 1;
                $requiredPointsForNextLevel = $badge->getRequiredPointsForLevel($nextLevel);
                
                if ($requiredPointsForNextLevel > $totalPoints) {
                $iconPath = null;
                if ($badge->icon && ! str_starts_with($badge->icon, 'pi-') && ! str_starts_with($badge->icon, 'fa-')) {
                    $iconPath = '/storage/'.$badge->icon;
                }

                    $upcoming->push([
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'icon_path' => $iconPath,
                        'required_points' => $requiredPointsForNextLevel,
                        'level' => $nextLevel,
                        'current_level' => $currentLevel,
                        'points_needed' => $requiredPointsForNextLevel - $totalPoints,
                        'progress_percentage' => round(($totalPoints / $requiredPointsForNextLevel) * 100, 2),
                    ]);
                }
            }
        }

        return $upcoming->sortBy('required_points')->take($limit)->values();
    }
}
