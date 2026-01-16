<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Difficulty;
use Illuminate\Database\Seeder;

class DifficultySeeder extends Seeder
{
    public function run(): void
    {
        $difficulties = [
            [
                'code' => 'easy',
                'name' => 'Легкая',
                'description' => 'Низкий уровень сложности для стартовых кейсов.',
            ],
            [
                'code' => 'medium',
                'name' => 'Средняя',
                'description' => 'Стандартная сложность для большинства кейсов.',
            ],
            [
                'code' => 'hard',
                'name' => 'Сложная',
                'description' => 'Повышенная сложность для опытных команд.',
            ],
        ];

        foreach ($difficulties as $difficulty) {
            Difficulty::updateOrCreate(
                ['code' => $difficulty['code']],
                $difficulty
            );
        }
    }
}
