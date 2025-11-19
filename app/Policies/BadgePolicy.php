<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;

class BadgePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('badges.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Badge $badge): bool
    {
        return $user->hasPermissionTo('badges.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('badges.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Badge $badge): bool
    {
        return $user->hasPermissionTo('badges.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Badge $badge): bool
    {
        return $user->hasPermissionTo('badges.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Badge $badge): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Badge $badge): bool
    {
        return $user->hasRole('admin');
    }
}
