<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\Simulator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        $partners = User::role('partner')->with('partnerProfile')->get();
        $simulators = Simulator::all(); // Используем все симуляторы, не только активные

        if ($partners->isEmpty()) {
            return;
        }

        // Генерируем шаблоны кейсов для разнообразия
        $caseTemplates = [
            // Банковские кейсы
            ['title' => 'Оптимизируй сеть в Ростове', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'banking'],
            ['title' => 'Разработай стратегию расширения филиалов', 'reward' => 'Стажировка + 10000 ₽', 'category' => 'banking'],
            ['title' => 'Оптимизируй финансовые потоки', 'reward' => 'Стажировка + менторство', 'category' => 'banking'],
            ['title' => 'Внедри систему управления рисками', 'reward' => 'Возможность трудоустройства', 'category' => 'banking'],
            ['title' => 'Разработай мобильное приложение для клиентов', 'reward' => 'Стажировка + 8000 ₽', 'category' => 'banking'],
            ['title' => 'Создай систему анализа кредитных рисков', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'banking'],
            ['title' => 'Оптимизируй процессы обслуживания клиентов', 'reward' => 'Денежный приз 12000 ₽', 'category' => 'banking'],
            
            // Логистические кейсы
            ['title' => 'Реши проблему логистики', 'reward' => 'Стажировка', 'category' => 'logistics'],
            ['title' => 'Оптимизируй маршруты доставки', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'logistics'],
            ['title' => 'Создай систему управления складом', 'reward' => 'Стажировка + 7000 ₽', 'category' => 'logistics'],
            ['title' => 'Разработай алгоритм распределения ресурсов', 'reward' => 'Денежный приз 12000 ₽', 'category' => 'logistics'],
            ['title' => 'Улучши систему отслеживания грузов', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'logistics'],
            ['title' => 'Оптимизируй цепочку поставок', 'reward' => 'Стажировка + менторство', 'category' => 'logistics'],
            
            // IT кейсы
            ['title' => 'Улучши клиентский опыт', 'reward' => 'Денежный приз 15000 ₽', 'category' => 'it'],
            ['title' => 'Внедри новую систему управления', 'reward' => 'Возможность трудоустройства', 'category' => 'it'],
            ['title' => 'Разработай план цифровизации', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'it'],
            ['title' => 'Создай систему аналитики данных', 'reward' => 'Стажировка + 9000 ₽', 'category' => 'it'],
            ['title' => 'Оптимизируй производительность сервисов', 'reward' => 'Стажировка + менторство', 'category' => 'it'],
            ['title' => 'Разработай систему безопасности', 'reward' => 'Денежный приз 18000 ₽', 'category' => 'it'],
            ['title' => 'Создай мобильное приложение', 'reward' => 'Стажировка + 8000 ₽', 'category' => 'it'],
            ['title' => 'Внедри облачную инфраструктуру', 'reward' => 'Стажировка + 10000 ₽', 'category' => 'it'],
            
            // Управленческие кейсы
            ['title' => 'Повысь эффективность команды', 'reward' => 'Стажировка', 'category' => 'management'],
            ['title' => 'Разработай стратегию развития компании', 'reward' => 'Стажировка + 10000 ₽', 'category' => 'management'],
            ['title' => 'Создай систему мотивации сотрудников', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'management'],
            ['title' => 'Оптимизируй бизнес-процессы', 'reward' => 'Возможность трудоустройства', 'category' => 'management'],
            ['title' => 'Разработай систему оценки эффективности', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'management'],
            
            // Маркетинговые кейсы
            ['title' => 'Разработай маркетинговую стратегию', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'marketing'],
            ['title' => 'Создай кампанию по привлечению клиентов', 'reward' => 'Стажировка + 7000 ₽', 'category' => 'marketing'],
            ['title' => 'Проанализируй конкурентную среду', 'reward' => 'Стажировка', 'category' => 'marketing'],
            ['title' => 'Разработай стратегию продвижения в соцсетях', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'marketing'],
            
            // Производственные кейсы
            ['title' => 'Оптимизируй производственные линии', 'reward' => 'Стажировка + 8000 ₽', 'category' => 'production'],
            ['title' => 'Снизь затраты на производство', 'reward' => 'Денежный приз 14000 ₽', 'category' => 'production'],
            ['title' => 'Внедри систему контроля качества', 'reward' => 'Стажировка + менторство', 'category' => 'production'],
            ['title' => 'Оптимизируй использование ресурсов', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'production'],
            
            // Розничные кейсы
            ['title' => 'Улучши управление запасами', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'retail'],
            ['title' => 'Разработай стратегию расширения сети', 'reward' => 'Возможность трудоустройства', 'category' => 'retail'],
            ['title' => 'Создай систему лояльности клиентов', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'retail'],
            ['title' => 'Оптимизируй ассортимент товаров', 'reward' => 'Стажировка + 7000 ₽', 'category' => 'retail'],
        ];

        // Неравномерное распределение кейсов по партнерам
        // Создаем массив с весами для каждого партнера (некоторые активнее, некоторые нет)
        $partnerWeights = [];
        foreach ($partners as $partner) {
            // 20% партнеров не имеют кейсов, 30% имеют 1-2, 30% имеют 3-5, 20% имеют 6-10
            $rand = fake()->numberBetween(1, 100);
            if ($rand <= 20) {
                $partnerWeights[$partner->id] = 0; // Нет кейсов
            } elseif ($rand <= 50) {
                $partnerWeights[$partner->id] = fake()->numberBetween(1, 2); // 1-2 кейса
            } elseif ($rand <= 80) {
                $partnerWeights[$partner->id] = fake()->numberBetween(3, 5); // 3-5 кейсов
            } else {
                $partnerWeights[$partner->id] = fake()->numberBetween(6, 10); // 6-10 кейсов
            }
        }

        // Общее количество кейсов: 53 (не круглое число)
        $totalCases = 53;
        $casesCreated = 0;

        $statusWeights = [
            'draft' => 10,      // 10% черновики
            'active' => 50,     // 50% активные
            'completed' => 25,  // 25% завершенные
            'archived' => 15,   // 15% архивированные
        ];

        // Создаем кейсы для каждого партнера согласно его весу
        foreach ($partners as $partner) {
            $casesForPartner = $partnerWeights[$partner->id];
            
            for ($i = 0; $i < $casesForPartner && $casesCreated < $totalCases; $i++) {
            // Разнообразные даты создания (от 8 месяцев назад до 1 месяца назад)
            $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');
            
            // Статус с весами
            $status = fake()->randomElement(array_merge(
                array_fill(0, $statusWeights['draft'], 'draft'),
                array_fill(0, $statusWeights['active'], 'active'),
                array_fill(0, $statusWeights['completed'], 'completed'),
                array_fill(0, $statusWeights['archived'], 'archived')
            ));

            // Дедлайн зависит от статуса
            if ($status === 'completed' || $status === 'archived') {
                // Завершенные/архивированные кейсы имеют прошедшие дедлайны
                $deadline = fake()->dateTimeBetween($createdAt, '-1 week');
            } elseif ($status === 'draft') {
                // Черновики могут иметь будущие дедлайны
                $deadline = fake()->dateTimeBetween('now', '+6 months');
            } else {
                // Активные кейсы имеют дедлайны в будущем
                $deadline = fake()->dateTimeBetween('now', '+4 months');
            }

            // Связь с симулятором (30% кейсов связаны с симуляторами)
            $simulatorId = null;
            if (fake()->boolean(30) && $simulators->isNotEmpty()) {
                $simulatorId = $simulators->random()->id;
            }

                // Выбираем случайный шаблон кейса
                $template = fake()->randomElement($caseTemplates);

                CaseModel::create([
                    'user_id' => $partner->id,
                    'title' => $template['title'],
                    'description' => fake()->paragraphs(5, true),
                    'simulator_id' => $simulatorId,
                    'deadline' => $deadline,
                    'reward' => $template['reward'],
                    'required_team_size' => fake()->numberBetween(2, 6),
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
                ]);

                $casesCreated++;
            }
        }

        // Если еще остались кейсы до нужного количества, создаем их случайным партнерам
        while ($casesCreated < $totalCases) {
            $partner = $partners->random();
            $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');
            
            $status = fake()->randomElement(array_merge(
                array_fill(0, $statusWeights['draft'], 'draft'),
                array_fill(0, $statusWeights['active'], 'active'),
                array_fill(0, $statusWeights['completed'], 'completed'),
                array_fill(0, $statusWeights['archived'], 'archived')
            ));

            if ($status === 'completed' || $status === 'archived') {
                $deadline = fake()->dateTimeBetween($createdAt, '-1 week');
            } elseif ($status === 'draft') {
                $deadline = fake()->dateTimeBetween('now', '+6 months');
            } else {
                $deadline = fake()->dateTimeBetween('now', '+4 months');
            }

            $simulatorId = null;
            if (fake()->boolean(30) && $simulators->isNotEmpty()) {
                $simulatorId = $simulators->random()->id;
            }

            $template = fake()->randomElement($caseTemplates);

            CaseModel::create([
                'user_id' => $partner->id,
                'title' => $template['title'],
                'description' => fake()->paragraphs(5, true),
                'simulator_id' => $simulatorId,
                'deadline' => $deadline,
                'reward' => $template['reward'],
                'required_team_size' => fake()->numberBetween(2, 6),
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
            ]);

            $casesCreated++;
        }

        // Создаем много кейсов для тестового партнера
        $testPartner = User::where('email', 'wer@wer.wer')->first();
        if ($testPartner) {
            // 15 кейсов для тестового партнера с разными статусами
            for ($i = 0; $i < 15; $i++) {
                $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');
                
                $status = fake()->randomElement(['draft', 'active', 'completed', 'archived']);
                
                if ($status === 'completed' || $status === 'archived') {
                    $deadline = fake()->dateTimeBetween($createdAt, '-1 week');
                } elseif ($status === 'draft') {
                    $deadline = fake()->dateTimeBetween('now', '+6 months');
                } else {
                    $deadline = fake()->dateTimeBetween('now', '+4 months');
                }

                $simulatorId = null;
                if (fake()->boolean(30) && $simulators->isNotEmpty()) {
                    $simulatorId = $simulators->random()->id;
                }

                $template = fake()->randomElement($caseTemplates);

                CaseModel::create([
                    'user_id' => $testPartner->id,
                    'title' => $template['title'],
                    'description' => fake()->paragraphs(5, true),
                    'simulator_id' => $simulatorId,
                    'deadline' => $deadline,
                    'reward' => $template['reward'],
                    'required_team_size' => fake()->numberBetween(2, 6),
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
                ]);
            }
        }
    }
}
