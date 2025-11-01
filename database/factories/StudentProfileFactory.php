<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        $faculties = [
            'Информационные технологии',
            'Экономика и менеджмент',
            'Строительство',
            'Автоматизация и робототехника',
            'Энергетика',
        ];

        $specializations = [
            'Программная инженерия',
            'Информационные системы',
            'Логистика',
            'Финансовый менеджмент',
            'Проектирование зданий',
            'Автоматизированные системы управления',
        ];

        return [
            'user_id' => User::factory(),
            'faculty' => fake()->randomElement($faculties),
            'group' => fake()->numerify('ИТ-##'),
            'specialization' => fake()->randomElement($specializations),
            'bio' => fake()->optional(0.6)->sentence(15),
            'total_points' => fake()->numberBetween(0, 5000),
        ];
    }
}

