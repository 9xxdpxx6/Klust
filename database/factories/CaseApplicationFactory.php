<?php

namespace Database\Factories;

use App\Models\ApplicationStatus;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseApplicationFactory extends Factory
{
    public function definition(): array
    {
        // Get status IDs
        $pendingId = ApplicationStatus::getIdByName('pending');
        $acceptedId = ApplicationStatus::getIdByName('accepted');
        $rejectedId = ApplicationStatus::getIdByName('rejected');

        $weights = [40, 35, 25]; // 40% pending, 35% accepted, 25% rejected

        return [
            'case_id' => CaseModel::factory(),
            'leader_id' => User::factory(),
            'motivation' => fake()->paragraph(3),
            'status_id' => fake()->randomElement(array_merge(
                array_fill(0, $weights[0], $pendingId),
                array_fill(0, $weights[1], $acceptedId),
                array_fill(0, $weights[2], $rejectedId)
            )),
            'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
