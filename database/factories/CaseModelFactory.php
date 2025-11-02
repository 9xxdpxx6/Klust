<?php

namespace Database\Factories;

use App\Models\CaseModel;
use App\Models\Partner;
use App\Models\Simulator;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseModelFactory extends Factory
{
    protected $model = CaseModel::class;

    public function definition(): array
    {
        $titles = [
            'Оптимизируй сеть в Ростове',
            'Разработай стратегию расширения',
            'Реши проблему логистики',
            'Улучши клиентский опыт',
            'Оптимизируй финансовые потоки',
            'Внедри новую систему управления',
            'Разработай план цифровизации',
            'Повысь эффективность команды',
        ];

        $rewards = [
            'Стажировка + 5000 ₽',
            'Стажировка + 10000 ₽',
            'Стажировка',
            'Денежный приз 15000 ₽',
            'Стажировка + менторство',
            'Возможность трудоустройства',
        ];

        return [
            'partner_id' => Partner::factory(),
            'title' => fake()->randomElement($titles),
            'description' => fake()->paragraph(5),
            'simulator_id' => null, // Будет установлено в сидере
            'deadline' => fake()->dateTimeBetween('now', '+3 months'),
            'reward' => fake()->randomElement($rewards),
            'required_team_size' => fake()->numberBetween(2, 5),
            'status' => fake()->randomElement(['draft', 'active', 'active', 'active', 'completed', 'archived']), // Больше active для реалистичности
        ];
    }
}

