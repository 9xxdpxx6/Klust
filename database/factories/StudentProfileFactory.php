<?php

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Cache;

class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        $specializations = [
            'Программная инженерия',
            'Информационные системы',
            'Логистика',
            'Финансовый менеджмент',
            'Проектирование зданий',
            'Автоматизированные системы управления',
        ];

        // Get faculty IDs to randomly assign
        $facultyIds = Cache::remember('faculty_ids', 3600, function () {
            return Faculty::pluck('id')->toArray();
        });

        return [
            'user_id' => User::factory(),
            'faculty_id' => !empty($facultyIds) ? fake()->randomElement($facultyIds) : null,
            'group' => fake()->numerify('ИТ-##'),
            'specialization' => fake()->randomElement($specializations),
            'bio' => fake()->optional(0.6)->sentence(15),
            'total_points' => fake()->numberBetween(0, 5000),
        ];
    }
}

