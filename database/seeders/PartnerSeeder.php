<?php

namespace Database\Seeders;

use App\Models\PartnerProfile;
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
            'Ростелеком',
            'Альфа-Банк',
            'X5 Retail Group',
            'Магнит',
            'Wildberries',
        ];

        foreach ($partners as $index => $partnerUser) {
            // Обновляем или создаем PartnerProfile
            $profile = $partnerUser->partnerProfile;
            if ($profile) {
                $profile->update([
                    'company_name' => $partnerNames[$index] ?? fake()->company(),
                    'description' => fake()->paragraph(2),
                    'is_active' => true,
                ]);
            } else {
                PartnerProfile::create([
                    'user_id' => $partnerUser->id,
                    'company_name' => $partnerNames[$index] ?? fake()->company(),
                    'description' => fake()->paragraph(2),
                    'is_active' => true,
                ]);
            }
        }
    }
}
