<?php

namespace Database\Seeders;

use App\Models\Simulator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimulatorSeeder extends Seeder
{
    public function run(): void
    {
        $partners = User::role('partner')->with('partnerProfile')->get();
        $simulatorsData = [
            ['title' => 'Банковская сеть: Оптимизация филиалов', 'partner' => 'Сбер'],
            ['title' => 'Логистический центр: Распределение ресурсов', 'partner' => 'Яндекс'],
            ['title' => 'IT-инфраструктура: Масштабирование сервисов', 'partner' => 'VK'],
            ['title' => 'Розничная сеть: Управление запасами', 'partner' => 'МТС'],
            ['title' => 'Производство: Оптимизация производственных линий', 'partner' => 'Ростех'],
        ];

        foreach ($simulatorsData as $index => $data) {
            $partner = $partners->first(function ($p) use ($data) {
                return $p->partnerProfile?->company_name === $data['partner'];
            }) ?? $partners->random();

            Simulator::create([
                'user_id' => $partner->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => fake()->paragraph(4),
                'preview_image' => null,
                'is_active' => $index === 0, // Только первый активен для MVP
            ]);
        }
    }
}
