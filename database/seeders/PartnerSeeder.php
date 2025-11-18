<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = User::role('partner')->get();
        $partnerNames = [
            'Сбер',
            'Тинькофф',
            'Яндекс',
            'Ростех',
            'Лукойл',
            'Газпром',
            'Роснефть',
            'МТС',
            'Мегафон',
            'VK',
        ];

        foreach ($partners as $index => $partnerUser) {
            Partner::create([
                'user_id' => $partnerUser->id,
                'name' => $partnerNames[$index] ?? fake()->company(),
                'logo' => null,
                'description' => fake()->paragraph(2),
                'is_active' => true,
            ]);
        }
    }
}
