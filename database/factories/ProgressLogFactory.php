<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProgressLog;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

class ProgressLogFactory extends Factory
{
    protected $model = ProgressLog::class;

    public function definition(): array
    {
        // Используем только экшны, которые реально используются в коде
        $actions = [
            'simulator_completion',
            'manual',
        ];

        $action = fake()->randomElement($actions);

        // Рассчитываем очки в зависимости от экшна
        $points = match ($action) {
            'simulator_completion' => fake()->numberBetween(50, 500), // Очки за завершение симулятора
            'manual' => fake()->numberBetween(10, 100), // Ручное начисление очков
            default => fake()->numberBetween(10, 100),
        };

        return [
            'user_id' => User::factory()->student(),
            'action' => $action,
            'loggable_type' => Skill::class, // Всегда Skill, так как логи прогресса только для навыков
            'loggable_id' => Skill::factory(), // Laravel автоматически создаст Skill и использует его ID
            'points_earned' => $points,
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Configure the factory to ensure user has student role
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProgressLog $progressLog) {
            // Ensure user is a student after creation
            $user = $progressLog->user;
            if ($user && ! $user->hasRole('student')) {
                $studentRole = Role::firstOrCreate(['name' => 'student']);
                $user->assignRole($studentRole);
            }
        });
    }
}
