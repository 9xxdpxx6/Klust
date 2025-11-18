<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgressLogFactory extends Factory
{
    public function definition(): array
    {
        $actions = [
            'completed_task',
            'earned_badge',
            'applied_to_case',
            'completed_simulator',
            'joined_team',
            'level_up_skill',
        ];

        $action = fake()->randomElement($actions);

        $points = match ($action) {
            'completed_task' => fake()->numberBetween(10, 50),
            'earned_badge' => fake()->numberBetween(50, 300),
            'applied_to_case' => fake()->numberBetween(5, 20),
            'completed_simulator' => fake()->numberBetween(100, 500),
            'joined_team' => fake()->numberBetween(15, 30),
            'level_up_skill' => fake()->numberBetween(25, 100),
            default => fake()->numberBetween(10, 50),
        };

        return [
            'user_id' => User::factory(),
            'action' => $action,
            'loggable_type' => fake()->randomElement([
                'App\\Models\\SimulatorSession',
                'App\\Models\\CaseApplication',
                'App\\Models\\Badge',
            ]),
            'loggable_id' => fake()->numberBetween(1, 100),
            'points_earned' => $points,
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
