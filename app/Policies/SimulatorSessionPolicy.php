<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SimulatorSession;
use App\Models\User;

class SimulatorSessionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SimulatorSession $session): bool
    {
        return $session->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SimulatorSession $session): bool
    {
        return $session->user_id === $user->id;
    }
}
