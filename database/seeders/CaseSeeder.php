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

        $casesData = [
            // Банковские кейсы
            ['title' => 'Оптимизируй сеть в Ростове', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'banking'],
            ['title' => 'Разработай стратегию расширения филиалов', 'reward' => 'Стажировка + 10000 ₽', 'category' => 'banking'],
            ['title' => 'Оптимизируй финансовые потоки', 'reward' => 'Стажировка + менторство', 'category' => 'banking'],
            ['title' => 'Внедри систему управления рисками', 'reward' => 'Возможность трудоустройства', 'category' => 'banking'],
            ['title' => 'Разработай мобильное приложение для клиентов', 'reward' => 'Стажировка + 8000 ₽', 'category' => 'banking'],
            
            // Логистические кейсы
            ['title' => 'Реши проблему логистики', 'reward' => 'Стажировка', 'category' => 'logistics'],
            ['title' => 'Оптимизируй маршруты доставки', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'logistics'],
            ['title' => 'Создай систему управления складом', 'reward' => 'Стажировка + 7000 ₽', 'category' => 'logistics'],
            ['title' => 'Разработай алгоритм распределения ресурсов', 'reward' => 'Денежный приз 12000 ₽', 'category' => 'logistics'],
            
            // IT кейсы
            ['title' => 'Улучши клиентский опыт', 'reward' => 'Денежный приз 15000 ₽', 'category' => 'it'],
            ['title' => 'Внедри новую систему управления', 'reward' => 'Возможность трудоустройства', 'category' => 'it'],
            ['title' => 'Разработай план цифровизации', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'it'],
            ['title' => 'Создай систему аналитики данных', 'reward' => 'Стажировка + 9000 ₽', 'category' => 'it'],
            ['title' => 'Оптимизируй производительность сервисов', 'reward' => 'Стажировка + менторство', 'category' => 'it'],
            ['title' => 'Разработай систему безопасности', 'reward' => 'Денежный приз 18000 ₽', 'category' => 'it'],
            
            // Управленческие кейсы
            ['title' => 'Повысь эффективность команды', 'reward' => 'Стажировка', 'category' => 'management'],
            ['title' => 'Разработай стратегию развития компании', 'reward' => 'Стажировка + 10000 ₽', 'category' => 'management'],
            ['title' => 'Создай систему мотивации сотрудников', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'management'],
            ['title' => 'Оптимизируй бизнес-процессы', 'reward' => 'Возможность трудоустройства', 'category' => 'management'],
            
            // Маркетинговые кейсы
            ['title' => 'Разработай маркетинговую стратегию', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'marketing'],
            ['title' => 'Создай кампанию по привлечению клиентов', 'reward' => 'Стажировка + 7000 ₽', 'category' => 'marketing'],
            ['title' => 'Проанализируй конкурентную среду', 'reward' => 'Стажировка', 'category' => 'marketing'],
            
            // Производственные кейсы
            ['title' => 'Оптимизируй производственные линии', 'reward' => 'Стажировка + 8000 ₽', 'category' => 'production'],
            ['title' => 'Снизь затраты на производство', 'reward' => 'Денежный приз 14000 ₽', 'category' => 'production'],
            ['title' => 'Внедри систему контроля качества', 'reward' => 'Стажировка + менторство', 'category' => 'production'],
            
            // Розничные кейсы
            ['title' => 'Улучши управление запасами', 'reward' => 'Стажировка + 5000 ₽', 'category' => 'retail'],
            ['title' => 'Разработай стратегию расширения сети', 'reward' => 'Возможность трудоустройства', 'category' => 'retail'],
            ['title' => 'Создай систему лояльности клиентов', 'reward' => 'Стажировка + 6000 ₽', 'category' => 'retail'],
        ];

        $statusWeights = [
            'draft' => 10,      // 10% черновики
            'active' => 50,     // 50% активные
            'completed' => 25,  // 25% завершенные
            'archived' => 15,   // 15% архивированные
        ];

        foreach ($casesData as $index => $caseData) {
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

            CaseModel::create([
                'user_id' => $partners->random()->id,
                'title' => $caseData['title'],
                'description' => fake()->paragraphs(5, true),
                'simulator_id' => $simulatorId,
                'deadline' => $deadline,
                'reward' => $caseData['reward'],
                'required_team_size' => fake()->numberBetween(2, 6),
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
            ]);
        }
    }
}
