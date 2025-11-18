<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $targetUser): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $targetUser): bool
    {
        return $user->hasPermissionTo('users.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Нельзя удалить самого себя
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Проверка права на удаление
        return $user->hasPermissionTo('users.delete');
    }
}
