<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    public function definition(): array
    {
        $badges = [
            ['name' => 'Логист-новичок', 'description' => 'Первый шаг в логистике', 'points' => 50, 'icon' => 'fa-map-marked-alt'],
            ['name' => 'Программист-начинающий', 'description' => 'Освоил основы программирования', 'points' => 100, 'icon' => 'fa-code'],
            ['name' => 'Аналитик данных', 'description' => 'Успешно проанализировал данные', 'points' => 150, 'icon' => 'fa-chart-line'],
            ['name' => 'Командный игрок', 'description' => 'Эффективно работал в команде', 'points' => 75, 'icon' => 'fa-users'],
            ['name' => 'Решение задач', 'description' => 'Решил 10 сложных задач', 'points' => 200, 'icon' => 'fa-lightbulb'],
            ['name' => 'Первый кейс', 'description' => 'Успешно прошёл первый кейс', 'points' => 250, 'icon' => 'fa-trophy'],
            ['name' => 'Мастер симуляторов', 'description' => 'Прошёл все симуляторы', 'points' => 500, 'icon' => 'fa-gamepad'],
            ['name' => 'Лидер команды', 'description' => 'Возглавил команду на кейсе', 'points' => 300, 'icon' => 'fa-crown'],
            ['name' => 'Стратег', 'description' => 'Разработал эффективную стратегию', 'points' => 400, 'icon' => 'fa-chess'],
            ['name' => 'Эксперт по оптимизации', 'description' => 'Оптимизировал процессы', 'points' => 350, 'icon' => 'fa-rocket'],
            ['name' => 'Неутомимый', 'description' => 'Провёл 20+ часов в симуляторах', 'points' => 450, 'icon' => 'fa-battery-full'],
            ['name' => 'Быстрый старт', 'description' => 'Получил первый бейдж за первую неделю', 'points' => 100, 'icon' => 'fa-bolt'],
            ['name' => 'Специалист по финансам', 'description' => 'Успешно решил финансовые задачи', 'points' => 200, 'icon' => 'fa-ruble-sign'],
            ['name' => 'Коммуникатор', 'description' => 'Эффективно общался с командой', 'points' => 150, 'icon' => 'fa-comments'],
            ['name' => 'Настойчивый', 'description' => 'Не сдался после 5 неудач', 'points' => 300, 'icon' => 'fa-fist-raised'],
        ];

        static $index = 0;
        $badge = $badges[$index % count($badges)];
        $index++;

        return [
            'name' => $badge['name'],
            'icon' => $badge['icon'],
            'description' => $badge['description'],
            'required_points' => $badge['points'],
        ];
    }
}

