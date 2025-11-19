<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    public function definition(): array
    {
        // Это не используется, т.к. сидер создаёт навыки напрямую
        return [
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['hard', 'soft']),
            'max_level' => 100,
        ];
    }
}
