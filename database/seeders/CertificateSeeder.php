<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certificate;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $certificates = [
            [
                'code' => 'case_completion_basic',
                'title' => 'Участник кейса',
                'description' => 'Сертификат за успешное участие в кейсе.',
                'icon' => null,
                'rarity' => 'common',
            ],
            [
                'code' => 'case_completion_advanced',
                'title' => 'Эксперт кейса',
                'description' => 'Сертификат за выполнение кейса повышенной сложности.',
                'icon' => null,
                'rarity' => 'rare',
            ],
            [
                'code' => 'case_completion_leader',
                'title' => 'Лидер команды',
                'description' => 'Сертификат за успешное лидерство в проекте.',
                'icon' => null,
                'rarity' => 'epic',
            ],
        ];

        foreach ($certificates as $certificate) {
            Certificate::updateOrCreate(
                ['code' => $certificate['code']],
                $certificate
            );
        }
    }
}
