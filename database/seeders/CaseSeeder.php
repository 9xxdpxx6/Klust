<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\Difficulty;
use App\Models\Simulator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        $partners = User::role('partner')->with('partnerProfile')->get();
        $difficultyIds = Difficulty::query()->pluck('id');
        $simulators = Simulator::all();

        if ($partners->isEmpty() || $difficultyIds->isEmpty()) {
            return;
        }

        $caseTemplates = [
            ['title' => 'Оптимизируй сеть в Ростове', 'category' => 'banking'],
            ['title' => 'Разработай стратегию расширения филиалов', 'category' => 'banking'],
            ['title' => 'Реши проблему логистики', 'category' => 'logistics'],
            ['title' => 'Оптимизируй маршруты доставки', 'category' => 'logistics'],
            ['title' => 'Улучши клиентский опыт', 'category' => 'it'],
            ['title' => 'Внедри систему управления рисками', 'category' => 'banking'],
            ['title' => 'Повысь эффективность команды', 'category' => 'management'],
            ['title' => 'Создай систему лояльности клиентов', 'category' => 'retail'],
        ];

        $statusWeights = [
            'draft' => 15,
            'active' => 55,
            'completed' => 20,
            'archived' => 10,
        ];

        foreach ($partners as $partner) {
            $casesForPartner = fake()->numberBetween(1, 4);

            for ($i = 0; $i < $casesForPartner; $i++) {
                $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');

                $status = fake()->randomElement(array_merge(
                    array_fill(0, $statusWeights['draft'], 'draft'),
                    array_fill(0, $statusWeights['active'], 'active'),
                    array_fill(0, $statusWeights['completed'], 'completed'),
                    array_fill(0, $statusWeights['archived'], 'archived')
                ));

                if ($status === 'completed' || $status === 'archived') {
                    $deadline = fake()->dateTimeBetween($createdAt, '-1 week');
                } elseif ($status === 'draft') {
                    $deadline = fake()->dateTimeBetween('now', '+6 months');
                } else {
                    $deadline = fake()->dateTimeBetween('now', '+4 months');
                }

                $simulatorId = null;
                if (fake()->boolean(30) && $simulators->isNotEmpty()) {
                    $simulatorId = $simulators->random()->id;
                }

                $template = fake()->randomElement($caseTemplates);

                CaseModel::create([
                    'user_id' => $partner->id,
                    'title' => $template['title'],
                    'description' => fake()->paragraphs(4, true),
                    'simulator_id' => $simulatorId,
                    'deadline' => $deadline,
                    'difficulty_id' => $difficultyIds->random(),
                    'required_team_size' => fake()->numberBetween(2, 6),
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
                ]);
            }
        }

    }
}
