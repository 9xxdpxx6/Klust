<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id && $notification->notifiable_type === User::class;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id && $notification->notifiable_type === User::class;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id && $notification->notifiable_type === User::class;
    }
}
