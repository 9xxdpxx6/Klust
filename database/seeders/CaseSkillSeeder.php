<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class CaseSkillSeeder extends Seeder
{
    public function run(): void
    {
        $cases = CaseModel::all();
        $skills = Skill::all();

        if ($cases->isEmpty() || $skills->isEmpty()) {
            $this->command->warn('Нет кейсов или навыков для связывания. Убедитесь, что CaseSeeder и SkillSeeder выполнены.');

            return;
        }

        foreach ($cases as $case) {
            // Каждый кейс получает от 2 до 5 случайных навыков
            $skillsCount = fake()->numberBetween(2, min(5, $skills->count()));
            $randomSkills = $skills->random($skillsCount);

            $case->skills()->sync($randomSkills->pluck('id')->toArray());
        }

        $this->command->info("Привязано навыков к кейсам: {$cases->count()} кейсов");
    }
}
