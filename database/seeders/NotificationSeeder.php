<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // ~200 уведомлений (по 1-3 на пользователя)
        foreach ($users as $user) {
            $notificationsCount = fake()->numberBetween(1, 3);
            
            AppNotification::factory($notificationsCount)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}

