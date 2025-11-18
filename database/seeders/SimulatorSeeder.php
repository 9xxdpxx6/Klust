<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Simulator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimulatorSeeder extends Seeder
{
    public function run(): void
    {
        $partners = Partner::all();
        $simulatorsData = [
            ['title' => 'Банковская сеть: Оптимизация филиалов', 'partner' => 'Сбер'],
            ['title' => 'Логистический центр: Распределение ресурсов', 'partner' => 'Яндекс'],
            ['title' => 'IT-инфраструктура: Масштабирование сервисов', 'partner' => 'VK'],
            ['title' => 'Розничная сеть: Управление запасами', 'partner' => 'МТС'],
            ['title' => 'Производство: Оптимизация производственных линий', 'partner' => 'Ростех'],
        ];

        foreach ($simulatorsData as $index => $data) {
            $partner = $partners->where('name', $data['partner'])->first() ?? $partners->random();

            Simulator::create([
                'partner_id' => $partner->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => fake()->paragraph(4),
                'preview_image' => null,
                'is_active' => $index === 0, // Только первый активен для MVP
            ]);
        }
    }
}
