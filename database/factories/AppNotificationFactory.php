<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppNotificationFactory extends Factory
{
    public function definition(): array
    {
        $types = ['task', 'case', 'badge', 'system'];

        $titles = [
            'task' => [
                'Новая задача в симуляторе',
                'Задача требует внимания',
                'Задача почти завершена',
            ],
            'case' => [
                'Новый кейс от партнёра',
                'Ваша заявка рассмотрена',
                'Команда принята на кейс',
            ],
            'badge' => [
                'Вы получили новый бейдж!',
                'Поздравляем с достижением',
                'Открыт новый бейдж',
            ],
            'system' => [
                'Обновление системы',
                'Важные изменения',
                'Новые возможности',
            ],
        ];

        $messages = [
            'task' => 'Проверьте новую задачу в симуляторе',
            'case' => 'Проверьте детали кейса',
            'badge' => 'Проверьте свой профиль для просмотра бейджа',
            'system' => 'Система была обновлена',
        ];

        $type = fake()->randomElement($types);
        $title = fake()->randomElement($titles[$type]);
        $link = match ($type) {
            'task' => '/learning/simulator',
            'case' => '/cases',
            'badge' => '/profile',
            'system' => '/',
            default => '/',
        };

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'message' => $messages[$type],
            'type' => $type,
            'link' => $link,
            'icon' => match ($type) {
                'task' => 'fa-tasks',
                'case' => 'fa-briefcase',
                'badge' => 'fa-award',
                'system' => 'fa-bell',
                default => 'fa-info-circle',
            },
            'action_text' => fake()->randomElement(['Перейти', 'Открыть', 'Просмотреть']),
            'is_read' => fake()->boolean(40),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
