<?php

namespace Database\Factories;

use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseApplicationFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['pending', 'accepted', 'rejected'];
        $weights = [40, 35, 25]; // 40% pending, 35% accepted, 25% rejected

        return [
            'case_id' => CaseModel::factory(),
            'leader_id' => User::factory(),
            'motivation' => fake()->paragraph(3),
            'status' => fake()->randomElement(array_merge(
                array_fill(0, $weights[0], $statuses[0]),
                array_fill(0, $weights[1], $statuses[1]),
                array_fill(0, $weights[2], $statuses[2])
            )),
            'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

