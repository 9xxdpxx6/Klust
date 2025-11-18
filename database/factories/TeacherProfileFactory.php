<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherProfileFactory extends Factory
{
    public function definition(): array
    {
        $departments = [
            'Кафедра информационных технологий',
            'Кафедра экономики',
            'Кафедра строительства',
            'Кафедра автоматизации',
            'Кафедра энергетики',
        ];

        $positions = [
            'Доцент',
            'Профессор',
            'Старший преподаватель',
            'Заведующий кафедрой',
        ];

        return [
            'user_id' => User::factory()->teacher(),
            'department' => fake()->randomElement($departments),
            'position' => fake()->randomElement($positions),
            'bio' => fake()->optional(0.7)->paragraph(2),
        ];
    }
}
