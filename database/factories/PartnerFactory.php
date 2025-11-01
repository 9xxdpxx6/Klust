<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    public function definition(): array
    {
        $partners = [
            ['name' => 'Сбер', 'logo' => null],
            ['name' => 'Тинькофф', 'logo' => null],
            ['name' => 'Яндекс', 'logo' => null],
            ['name' => 'Ростех', 'logo' => null],
            ['name' => 'Лукойл', 'logo' => null],
            ['name' => 'Газпром', 'logo' => null],
            ['name' => 'Роснефть', 'logo' => null],
            ['name' => 'МТС', 'logo' => null],
            ['name' => 'Мегафон', 'logo' => null],
            ['name' => 'VK', 'logo' => null],
        ];

        static $index = 0;
        $partner = $partners[$index % count($partners)];
        $index++;

        return [
            'user_id' => null, // Будет установлено в сидере
            'name' => $partner['name'],
            'logo' => $partner['logo'],
            'description' => fake()->paragraph(2),
            'is_active' => fake()->boolean(90),
        ];
    }
}

