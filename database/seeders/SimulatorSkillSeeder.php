<?php

namespace Database\Seeders;

use App\Models\Simulator;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class SimulatorSkillSeeder extends Seeder
{
    public function run(): void
    {
        $bankSimulator = Simulator::where('slug', 'bankovskaya-set-optimizaciya-filialov')->first();

        if (!$bankSimulator) {
            return;
        }

        // Skills relevant to the bank simulator:
        // - Коммуникация (customer interaction)
        // - Финансовая грамотность (financial products knowledge)
        // - Решение задач (problem solving during consultation)
        // - Критическое мышление (analyzing client needs)
        $skillNames = [
            'Коммуникация',
            'Финансовая грамотность',
            'Решение задач',
            'Критическое мышление',
        ];

        $skillIds = Skill::whereIn('name', $skillNames)->pluck('id');

        if ($skillIds->isNotEmpty()) {
            $bankSimulator->skills()->syncWithoutDetaching($skillIds);
        }
    }
}
