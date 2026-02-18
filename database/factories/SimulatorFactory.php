<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SimulatorFactory extends Factory
{
    public function definition(): array
    {
        // Всегда возвращаем данные для одного единственного симулятора
        $title = 'Банковская сеть: Оптимизация филиалов';
        $description = 'Симулятор моделирует работу сети банковских отделений в условиях изменения клиентского спроса, цифровизации услуг и оптимизации издержек. Участники анализируют географическое распределение филиалов, принимают решения о закрытии, открытии или модернизации точек, оценивают влияние на доступность услуг и прибыльность. Цель — достичь баланса между охватом, эффективностью и клиентской лояльностью.';

        return [
            'user_id' => 1, // Будет установлено в сидере
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $description,
            'preview_image' => null,
            'is_active' => true,
        ];
    }
}
