<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём 10 навыков с разными максимальными уровнями (50-100 с шагом 10)
        $skillsData = [
            ['name' => 'Критическое мышление', 'category' => 'soft', 'max_level' => 80],
            ['name' => 'Решение задач', 'category' => 'soft', 'max_level' => 90],
            ['name' => 'Коммуникация', 'category' => 'soft', 'max_level' => 70],
            ['name' => 'Работа в команде', 'category' => 'soft', 'max_level' => 80],
            ['name' => 'Проектное управление', 'category' => 'soft', 'max_level' => 100],
            ['name' => 'Анализ данных', 'category' => 'hard', 'max_level' => 100],
            ['name' => 'Финансовая грамотность', 'category' => 'hard', 'max_level' => 90],
            ['name' => 'Основы автоматизации', 'category' => 'hard', 'max_level' => 100],
            ['name' => 'Тайм-менеджмент', 'category' => 'soft', 'max_level' => 60],
            ['name' => 'Работа в условиях неопределённости', 'category' => 'soft', 'max_level' => 70],
        ];

        foreach ($skillsData as $skillData) {
            Skill::create([
                'name' => $skillData['name'],
                'category' => $skillData['category'],
                'max_level' => $skillData['max_level'],
            ]);
        }
    }
}
