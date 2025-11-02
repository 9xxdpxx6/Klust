<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\Partner;
use App\Models\Simulator;
use Illuminate\Database\Seeder;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        $partners = Partner::all();
        $simulators = Simulator::where('is_active', true)->get();

        $casesData = [
            ['title' => 'Оптимизируй сеть в Ростове', 'reward' => 'Стажировка + 5000 ₽'],
            ['title' => 'Разработай стратегию расширения', 'reward' => 'Стажировка + 10000 ₽'],
            ['title' => 'Реши проблему логистики', 'reward' => 'Стажировка'],
            ['title' => 'Улучши клиентский опыт', 'reward' => 'Денежный приз 15000 ₽'],
            ['title' => 'Оптимизируй финансовые потоки', 'reward' => 'Стажировка + менторство'],
            ['title' => 'Внедри новую систему управления', 'reward' => 'Возможность трудоустройства'],
            ['title' => 'Разработай план цифровизации', 'reward' => 'Стажировка + 5000 ₽'],
            ['title' => 'Повысь эффективность команды', 'reward' => 'Стажировка'],
        ];

        foreach ($casesData as $index => $caseData) {
            CaseModel::create([
                'partner_id' => $partners->random()->id,
                'title' => $caseData['title'],
                'description' => fake()->paragraph(5),
                'simulator_id' => $index < 3 ? $simulators->random()->id : null, // Первые 3 связаны с симуляторами
                'deadline' => fake()->dateTimeBetween('now', '+3 months'),
                'reward' => $caseData['reward'],
                'required_team_size' => fake()->numberBetween(2, 5),
                'status' => fake()->randomElement(['draft', 'active', 'active', 'active', 'completed']), // Больше active для реалистичности
            ]);
        }
    }
}

