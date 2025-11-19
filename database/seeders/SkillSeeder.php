<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём 10 навыков (уникальные, через фабрику)
        $skillsData = [
            ['name' => 'Логистика', 'category' => 'hard'],
            ['name' => 'Программирование', 'category' => 'hard'],
            ['name' => 'Финансовая математика', 'category' => 'hard'],
            ['name' => 'Анализ данных', 'category' => 'hard'],
            ['name' => 'Проектное управление', 'category' => 'soft'],
            ['name' => 'Работа в команде', 'category' => 'soft'],
            ['name' => 'Коммуникации', 'category' => 'soft'],
            ['name' => 'Решение задач', 'category' => 'soft'],
            ['name' => 'Критическое мышление', 'category' => 'soft'],
            ['name' => 'Английский язык', 'category' => 'hard'],
        ];

        foreach ($skillsData as $skillData) {
            Skill::create([
                'name' => $skillData['name'],
                'category' => $skillData['category'],
                'max_level' => 100,
            ]);
        }
    }
}
