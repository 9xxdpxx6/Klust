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
//        $partners = User::role('partner')->with('partnerProfile')->get();
//        $simulators = Simulator::all();
//
//        if ($partners->isEmpty()) {
//            return;
//        }
//
//        // Шаблоны кейсов с ОПИСАНИЯМИ, соответствующими задачам
//        $caseTemplates = [
//            // === Банковские кейсы ===
//            [
//                'title' => 'Оптимизируй сеть в Ростове',
//                'reward' => 'Стажировка + 5000 ₽',
//                'category' => 'banking',
//                'description' => 'В Ростове-на-Дону наблюдается неравномерная загрузка отделений: в центре — перегруз, в отдалённых районах — низкая посещаемость. Необходимо проанализировать геоданные, трафик клиентов, издержки и предложить оптимальную конфигурацию сети: какие отделения модернизировать, какие закрыть, где открыть новые точки или digital-зоны. Решение должно повысить доступность услуг и снизить операционные расходы на 15%.',
//            ],
//            [
//                'title' => 'Разработай стратегию расширения филиалов',
//                'reward' => 'Стажировка + 10000 ₽',
//                'category' => 'banking',
//                'description' => 'Банк планирует выйти в 5 новых регионов за 2 года. Задача — разработать поэтапную стратегию открытия филиалов с учётом конкуренции, покупательской способности населения, логистики и цифровой зрелости регионов. Требуется обосновать приоритеты, предложить форматы отделений (full-service, digital, pop-up), рассчитать ROI и срок окупаемости.',
//            ],
//            [
//                'title' => 'Оптимизируй финансовые потоки',
//                'reward' => 'Стажировка + менторство',
//                'category' => 'banking',
//                'description' => 'Внутренние финансовые потоки между подразделениями несогласованы: избыточные резервы в одних подразделениях при дефиците ликвидности в других. Нужно предложить модель централизованного казначейства, алгоритм daily cash pooling, а также инструменты прогнозирования кассовых разрывов. Цель — снизить стоимость фондирования на 20% и сократить idle cash на 30%.',
//            ],
//            [
//                'title' => 'Внедри систему управления рисками',
//                'reward' => 'Возможность трудоустройства',
//                'category' => 'banking',
//                'description' => 'Требуется разработать и внедрить систему операционного риск-менеджмента для розничного банка, включая идентификацию рисков (мошенничество, ошибки персонала, ИТ-сбои), оценку их вероятности и воздействия, а также предложения по контролю и мониторингу. Решение должно соответствовать требованиям ЦБ РФ и быть интегрируемым в существующую ИТ-инфраструктуру.',
//            ],
//            [
//                'title' => 'Разработай мобильное приложение для клиентов',
//                'reward' => 'Стажировка + 8000 ₽',
//                'category' => 'banking',
//                'description' => 'Сейчас 60% клиентов пользуются мобильным банком, но NPS ниже среднего по рынку. Задача — провести анализ болей пользователей, предложить улучшения UX/UI, внедрить 2–3 «killer features» (например, финансовый ассистент, персонализированные предложения, offline-режим), а также рассчитать экономическую эффективность изменений.',
//            ],
//            [
//                'title' => 'Создай систему анализа кредитных рисков',
//                'reward' => 'Стажировка + 6000 ₽',
//                'category' => 'banking',
//                'description' => 'Уровень просроченной задолженности в сегменте потребкредитования вырос на 12% за год. Необходимо разработать скоринговую модель с использованием альтернативных данных (поведенческие, транзакционные, open banking), протестировать её на исторических данных и предложить пороги принятия решений. Модель должна повысить точность прогноза дефолта на 15% при сохранении конверсии.',
//            ],
//            [
//                'title' => 'Оптимизируй процессы обслуживания клиентов',
//                'reward' => 'Денежный приз 12000 ₽',
//                'category' => 'banking',
//                'description' => 'Среднее время обслуживания в отделении — 22 минуты, удовлетворённость клиентов — 74%. Нужно провести time-motion анализ, выявить узкие места, предложить изменения в layout офиса, распределении ролей сотрудников и внедрении self-service решений. Цель — сократить время до 12 минут и поднять NPS до 85+.',
//            ],
//
//            // === Логистические кейсы ===
//            [
//                'title' => 'Реши проблему логистики',
//                'reward' => 'Стажировка',
//                'category' => 'logistics',
//                'description' => 'Последняя миля составляет 45% от общей стоимости доставки. В пиковые дни 30% заказов доставляются с опозданием. Требуется проанализировать текущую схему (хабы, курьеры, маршрутизация), предложить решения: микродепо, dark stores, крауд-доставка, dynamic pricing. Решение должно снизить cost per delivery на 25% и повысить on-time delivery до 98%.',
//            ],
//            [
//                'title' => 'Оптимизируй маршруты доставки',
//                'reward' => 'Стажировка + 6000 ₽',
//                'category' => 'logistics',
//                'description' => 'Компания использует устаревший алгоритм маршрутизации без учёта пробок, ограничений по времени разгрузки и грузоподъёмности. Задача — разработать и протестировать новый алгоритм (например, на основе CVRP с временными окнами), интегрировать данные Яндекс.Пробок и ограничений ТС. Цель — сократить пробег на 18% и увеличить количество доставок на курьера в день на 20%.',
//            ],
//            [
//                'title' => 'Создай систему управления складом',
//                'reward' => 'Стажировка + 7000 ₽',
//                'category' => 'logistics',
//                'description' => 'На складе 120 тыс. SKU, но только 65% мест хранения используются эффективно. Необходимо предложить систему зонирования (A-B-C анализ), алгоритм размещения товаров с учётом частоты подборки и совместимости, а также внедрение голосовой комплектации. Результат — сократить время подборки заказа на 30% и увеличить плотность хранения на 20%.',
//            ],
//            [
//                'title' => 'Разработай алгоритм распределения ресурсов',
//                'reward' => 'Денежный приз 12000 ₽',
//                'category' => 'logistics',
//                'description' => 'В распределительном центре 50 единиц техники (погрузчики, конвейеры, роботы), но загрузка неравномерна: в пик — 95%, в off-peak — 30%. Требуется создать динамическую модель распределения ресурсов по зонам в зависимости от прогноза поступления заказов, включая перераспределение персонала и техники в режиме реального времени.',
//            ],
//
//            // === IT-кейсы === (пример — можно продолжить по аналогии)
//            [
//                'title' => 'Улучши клиентский опыт',
//                'reward' => 'Денежный приз 15000 ₽',
//                'category' => 'it',
//                'description' => 'В приложении высокий уровень оттока на этапе онбординга: 68% пользователей не доходят до первого полезного действия. Задача — провести анализ funnel, A/B-тестирование альтернативных сценариев первого запуска, внедрить personalization engine и push-триггеры. Цель — повысить retention Day7 на 25%.',
//            ],
//            [
//                'title' => 'Оптимизируй производительность сервисов',
//                'reward' => 'Стажировка + менторство',
//                'category' => 'it',
//                'description' => 'Среднее время ответа API — 850 мс, при пиковой нагрузке — до 3.2 с. Необходимо провести profiling, выявить «горячие» точки (медленные SQL, N+1, блокировки), предложить архитектурные изменения (кеширование, read replicas, async processing). Требуется снизить p95 latency до 400 мс без увеличения инфраструктурных затрат.',
//            ],
//
//            // === Управленческие, маркетинговые и др. — можно добавить по такому же принципу ===
//            // Ниже — 2 примера для демонстрации:
//            [
//                'title' => 'Повысь эффективность команды',
//                'reward' => 'Стажировка',
//                'category' => 'management',
//                'description' => 'Команда из 12 аналитиков тратит 40% времени на рутинные задачи (сбор данных, отчёты). Требуется провести анализ workflow, автоматизировать повторяющиеся процессы (ETL, dashboards), внедрить систему OKR и регулярную обратную связь. Цель — освободить 20+ часов в неделю на стратегические задачи и снизить turnover на 50% за год.',
//            ],
//            [
//                'title' => 'Создай систему лояльности клиентов',
//                'reward' => 'Стажировка + 6000 ₽',
//                'category' => 'retail',
//                'description' => 'Уровень повторных покупок — 31%, LTV ниже конкурентов на 22%. Нужно разработать многоуровневую программу лояльности с персонализированными вознаграждениями, gamification-элементами и интеграцией с mobile app. Важно обеспечить ROI > 1.5 в первый год и рост частоты покупок на 25%.',
//            ],
//        ];
//
//        // ——— Логика распределения остаётся без изменений ———
//
//        $partnerWeights = [];
//        foreach ($partners as $partner) {
//            $rand = fake()->numberBetween(1, 100);
//            if ($rand <= 20) {
//                $partnerWeights[$partner->id] = 0;
//            } elseif ($rand <= 50) {
//                $partnerWeights[$partner->id] = fake()->numberBetween(1, 2);
//            } elseif ($rand <= 80) {
//                $partnerWeights[$partner->id] = fake()->numberBetween(3, 5);
//            } else {
//                $partnerWeights[$partner->id] = fake()->numberBetween(6, 10);
//            }
//        }
//
//        $totalCases = 53;
//        $casesCreated = 0;
//
//        $statusWeights = [
//            'draft' => 10,
//            'active' => 50,
//            'completed' => 25,
//            'archived' => 15,
//        ];
//
//        foreach ($partners as $partner) {
//            $casesForPartner = $partnerWeights[$partner->id];
//            for ($i = 0; $i < $casesForPartner && $casesCreated < $totalCases; $i++) {
//                $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');
//
//                $status = fake()->randomElement(array_merge(
//                    array_fill(0, $statusWeights['draft'], 'draft'),
//                    array_fill(0, $statusWeights['active'], 'active'),
//                    array_fill(0, $statusWeights['completed'], 'completed'),
//                    array_fill(0, $statusWeights['archived'], 'archived')
//                ));
//
//                if ($status === 'completed' || $status === 'archived') {
//                    $deadline = fake()->dateTimeBetween($createdAt, '-1 week');
//                } elseif ($status === 'draft') {
//                    $deadline = fake()->dateTimeBetween('now', '+6 months');
//                } else {
//                    $deadline = fake()->dateTimeBetween('now', '+4 months');
//                }
//
//                $simulatorId = null;
//                if (fake()->boolean(30) && $simulators->isNotEmpty()) {
//                    $simulatorId = $simulators->random()->id;
//                }
//
//                $template = fake()->randomElement($caseTemplates);
//
//                CaseModel::create([
//                    'user_id' => $partner->id,
//                    'title' => $template['title'],
//                    'description' => $template['description'], // ← Теперь осмысленное!
//                    'simulator_id' => $simulatorId,
//                    'deadline' => $deadline,
//                    'reward' => $template['reward'],
//                    'required_team_size' => fake()->numberBetween(2, 6),
//                    'status' => $status,
//                    'created_at' => $createdAt,
//                    'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
//                ]);
//
//                $casesCreated++;
//            }
//        }
//
//        // Дополнительные кейсы до 53
//        while ($casesCreated < $totalCases) {
//            $partner = $partners->random();
//            $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');
//
//            $status = fake()->randomElement(array_merge(
//                array_fill(0, $statusWeights['draft'], 'draft'),
//                array_fill(0, $statusWeights['active'], 'active'),
//                array_fill(0, $statusWeights['completed'], 'completed'),
//                array_fill(0, $statusWeights['archived'], 'archived')
//            ));
//
//            if ($status === 'completed' || $status === 'archived') {
//                $deadline = fake()->dateTimeBetween($createdAt, '-1 week');
//            } elseif ($status === 'draft') {
//                $deadline = fake()->dateTimeBetween('now', '+6 months');
//            } else {
//                $deadline = fake()->dateTimeBetween('now', '+4 months');
//            }
//
//            $simulatorId = null;
//            if (fake()->boolean(30) && $simulators->isNotEmpty()) {
//                $simulatorId = $simulators->random()->id;
//            }
//
//            $template = fake()->randomElement($caseTemplates);
//
//            CaseModel::create([
//                'user_id' => $partner->id,
//                'title' => $template['title'],
//                'description' => $template['description'],
//                'simulator_id' => $simulatorId,
//                'deadline' => $deadline,
//                'reward' => $template['reward'],
//                'required_team_size' => fake()->numberBetween(2, 6),
//                'status' => $status,
//                'created_at' => $createdAt,
//                'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
//            ]);
//
//            $casesCreated++;
//        }
//
//        // ——— Тестовый партнёр ———
//        $testPartner = User::where('email', 'wer@wer.wer')->first();
//        if ($testPartner) {
//            for ($i = 0; $i < 15; $i++) {
//                $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');
//                $status = fake()->randomElement(['draft', 'active', 'completed', 'archived']);
//
//                if ($status === 'completed' || $status === 'archived') {
//                    $deadline = fake()->dateTimeBetween($createdAt, '-1 week');
//                } elseif ($status === 'draft') {
//                    $deadline = fake()->dateTimeBetween('now', '+6 months');
//                } else {
//                    $deadline = fake()->dateTimeBetween('now', '+4 months');
//                }
//
//                $simulatorId = null;
//                if (fake()->boolean(30) && $simulators->isNotEmpty()) {
//                    $simulatorId = $simulators->random()->id;
//                }
//
//                $template = fake()->randomElement($caseTemplates);
//
//                CaseModel::create([
//                    'user_id' => $testPartner->id,
//                    'title' => $template['title'],
//                    'description' => $template['description'],
//                    'simulator_id' => $simulatorId,
//                    'deadline' => $deadline,
//                    'reward' => $template['reward'],
//                    'required_team_size' => fake()->numberBetween(2, 6),
//                    'status' => $status,
//                    'created_at' => $createdAt,
//                    'updated_at' => $status === 'draft' ? $createdAt : fake()->dateTimeBetween($createdAt, 'now'),
//                ]);
//            }
//        }
    }
}
