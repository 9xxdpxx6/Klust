<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AppNotification;
use App\Models\User;

class AppNotificationPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AppNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AppNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AppNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
