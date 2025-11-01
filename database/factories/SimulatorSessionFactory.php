<?php

namespace Database\Factories;

use App\Models\Simulator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimulatorSessionFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-2 months', 'now');
        $isCompleted = fake()->boolean(70);
        $completedAt = $isCompleted ? fake()->dateTimeBetween($startedAt, 'now') : null;
        $timeSpent = $isCompleted ? fake()->numberBetween(300, 7200) : fake()->numberBetween(60, 1800);

        return [
            'user_id' => User::factory(),
            'simulator_id' => Simulator::factory(),
            'state' => $isCompleted ? [
                'level' => fake()->numberBetween(1, 10),
                'completed_tasks' => fake()->numberBetween(1, 5),
                'score_multiplier' => fake()->randomFloat(2, 1.0, 2.5),
            ] : null,
            'score' => $isCompleted ? fake()->numberBetween(50, 1000) : fake()->numberBetween(0, 49),
            'time_spent' => $timeSpent,
            'is_completed' => $isCompleted,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }
}

