<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('skills.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo('skills.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('skills.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo('skills.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo('skills.delete');
    }
}
