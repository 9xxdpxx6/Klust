<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            [
                'name' => 'Факультет информационных технологий',
                'code' => 'FIT',
                'description' => 'Подготовка специалистов в области IT и компьютерных наук',
            ],
            [
                'name' => 'Факультет экономики и финансов',
                'code' => 'FEF',
                'description' => 'Подготовка экономистов, финансистов и менеджеров',
            ],
            [
                'name' => 'Факультет строительства и архитектуры',
                'code' => 'FCA',
                'description' => 'Подготовка архитекторов, строителей и дизайнеров',
            ],
            [
                'name' => 'Факультет машиностроения и транспорта',
                'code' => 'FMT',
                'description' => 'Подготовка инженеров-механиков и транспортных специалистов',
            ],
            [
                'name' => 'Факультет энергетики и электротехники',
                'code' => 'FEE',
                'description' => 'Подготовка энергетиков и электротехников',
            ],
        ];

        foreach ($faculties as $faculty) {
            Faculty::updateOrCreate(
                ['code' => $faculty['code']],
                $faculty
            );
        }
    }
}
