<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SimulatorFactory extends Factory
{
    public function definition(): array
    {
        $titles = [
            'Банковская сеть: Оптимизация филиалов',
            'Логистический центр: Распределение ресурсов',
            'IT-инфраструктура: Масштабирование сервисов',
            'Розничная сеть: Управление запасами',
            'Производство: Оптимизация производственных линий',
        ];

        $title = fake()->randomElement($titles);

        return [
            'partner_id' => 1, // Будет установлено в сидере
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(4),
            'preview_image' => fake()->optional(0.7)->imageUrl(800, 600, 'business'),
            'is_active' => fake()->boolean(80),
        ];
    }
}
