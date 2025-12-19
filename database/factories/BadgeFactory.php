<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    public function definition(): array
    {
        $badges = [
            ['name' => 'Логист-новичок', 'description' => 'Первый шаг в логистике', 'points' => 50, 'icon' => 'pi-map-marker'],
            ['name' => 'Программист-начинающий', 'description' => 'Освоил основы программирования', 'points' => 100, 'icon' => 'pi-code'],
            ['name' => 'Аналитик данных', 'description' => 'Успешно проанализировал данные', 'points' => 150, 'icon' => 'pi-chart-bar'],
            ['name' => 'Командный игрок', 'description' => 'Эффективно работал в команде', 'points' => 75, 'icon' => 'pi-users'],
            ['name' => 'Решение задач', 'description' => 'Решил 10 сложных задач', 'points' => 200, 'icon' => 'pi-lightbulb'],
            ['name' => 'Первый кейс', 'description' => 'Успешно прошёл первый кейс', 'points' => 250, 'icon' => 'pi-trophy'],
            ['name' => 'Мастер симуляторов', 'description' => 'Прошёл все симуляторы', 'points' => 500, 'icon' => 'pi-cog'],
            ['name' => 'Лидер команды', 'description' => 'Возглавил команду на кейсе', 'points' => 300, 'icon' => 'pi-star'],
            ['name' => 'Стратег', 'description' => 'Разработал эффективную стратегию', 'points' => 400, 'icon' => 'pi-th-large'],
            ['name' => 'Эксперт по оптимизации', 'description' => 'Оптимизировал процессы', 'points' => 350, 'icon' => 'pi-arrow-up'],
            ['name' => 'Неутомимый', 'description' => 'Провёл 20+ часов в симуляторах', 'points' => 450, 'icon' => 'pi-circle'],
            ['name' => 'Быстрый старт', 'description' => 'Получил первое достижение за первую неделю', 'points' => 100, 'icon' => 'pi-check-circle'],
            ['name' => 'Специалист по финансам', 'description' => 'Успешно решил финансовые задачи', 'points' => 200, 'icon' => 'pi-dollar'],
            ['name' => 'Коммуникатор', 'description' => 'Эффективно общался с командой', 'points' => 150, 'icon' => 'pi-comment'],
            ['name' => 'Настойчивый', 'description' => 'Не сдался после 5 неудач', 'points' => 300, 'icon' => 'pi-thumbs-up'],
        ];

        static $index = 0;
        $badge = $badges[$index % count($badges)];
        $index++;

        return [
            'name' => $badge['name'],
            'icon' => $badge['icon'],
            'description' => $badge['description'],
            'required_points' => $badge['points'],
            'points_increment' => fake()->numberBetween(50, 200), // Инкремент от 50 до 200 очков на уровень
            'max_level' => fake()->boolean(70) ? null : fake()->numberBetween(3, 10), // 70% безлимит, 30% с макс уровнем 3-10
        ];
    }
}
