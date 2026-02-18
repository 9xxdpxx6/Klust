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
        // Создаем только ОДИН симулятор
        $partners = User::role('partner')->with('partnerProfile')->get();

        if ($partners->isEmpty()) {
            return;
        }

        // Ищем партнера "Сбер" или берем первого доступного
        $partner = $partners->first(function ($p) {
            return $p->partnerProfile?->company_name === 'Сбер';
        }) ?? $partners->first();

        // Используем firstOrCreate для гарантии одного симулятора
        Simulator::firstOrCreate(
            ['slug' => 'bankovskaya-set-optimizaciya-filialov'],
            [
                'user_id' => $partner->id,
                'title' => 'Банковская сеть: Оптимизация филиалов',
                'description' => 'Симулятор моделирует работу сети банковских отделений в условиях изменения клиентского спроса, цифровизации услуг и оптимизации издержек. Участники анализируют географическое распределение филиалов, принимают решения о закрытии, открытии или модернизации точек, оценивают влияние на доступность услуг и прибыльность. Цель — достичь баланса между охватом, эффективностью и клиентской лояльностью.',
                'preview_image' => null,
                'is_active' => true,
            ]
        );
    }
}
