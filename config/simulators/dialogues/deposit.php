<?php

declare(strict_types=1);

return [
    'max_score' => 125,
    'stages' => [

        /*
        |--------------------------------------------------------------------------
        | 1. Приветствие
        |--------------------------------------------------------------------------
        */

        'greeting' => [
            'client_message' => 'Добрый день! Хочу разместить деньги на вкладе.',
            'user_options' => [
                [
                    'id' => 'greet_warm',
                    'text' => 'Здравствуйте! Конечно, присаживайтесь. Расскажите, какую сумму и на какой срок рассматриваете?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Вежливое приветствие и уточнение цели'],
                    ],
                ],
                [
                    'id' => 'greet_push',
                    'text' => 'Добрый день! У нас сейчас повышенная ставка — давайте быстро оформим!',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -5, 'category' => 'service_quality', 'reason' => 'Давление без выяснения потребности'],
                    ],
                ],
            ],
            'next_stage' => [
                'greet_warm' => 'client_goal',
                'greet_push' => 'client_pushback',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 1а. Реакция клиента на давление
        |--------------------------------------------------------------------------
        */

        'client_pushback' => [
            'client_message' => 'Подождите, я хочу сначала понять условия. Не нужно торопить.',
            'user_options' => [
                [
                    'id' => 'recover_polite',
                    'text' => 'Конечно, извините. Давайте обсудим ваши цели — что для вас важнее: максимальная доходность или гибкость?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Исправление ситуации'],
                    ],
                ],
                [
                    'id' => 'recover_dry',
                    'text' => 'Хорошо, слушаю.',
                    'actions' => [],
                ],
            ],
            'next_stage' => [
                'recover_polite' => 'client_goal',
                'recover_dry' => 'client_goal',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 2. Цель вклада
        |--------------------------------------------------------------------------
        */

        'client_goal' => [
            'client_message' => 'Хочу положить около 500 тысяч. Цель — накопить на первоначальный взнос по ипотеке через пару лет.',
            'user_options' => [
                [
                    'id' => 'goal_structured',
                    'text' => 'Понял, значит горизонт 2 года. Чтобы подобрать оптимальный продукт, уточню пару моментов. Планируете пополнять вклад?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Структурированный подход: выяснение срока и параметров'],
                    ],
                ],
                [
                    'id' => 'goal_quick',
                    'text' => '500 тысяч на 2 года — хорошо. Пополнять будете?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 7, 'category' => 'correctness', 'reason' => 'Быстрый переход к делу'],
                    ],
                ],
                [
                    'id' => 'goal_promise',
                    'text' => '500 тысяч? У нас отличная ставка 12%. Давайте сразу оформим!',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Навязывание продукта без выяснения потребности'],
                    ],
                ],
            ],
            'next_stage' => [
                'goal_structured' => 'client_replenishment',
                'goal_quick' => 'client_replenishment',
                'goal_promise' => 'client_replenishment',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 3. Пополнение и частичное снятие
        |--------------------------------------------------------------------------
        */

        'client_replenishment' => [
            'client_message' => 'Да, хотел бы ежемесячно откладывать тысяч по 20–30. И чтобы в случае чего можно было снять часть.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'deposit_amount', 'value' => 500000],
                ['type' => 'update_client_data', 'field' => 'deposit_period_months', 'value' => 24],
            ],
            'user_options' => [
                [
                    'id' => 'explain_tradeoff',
                    'text' => 'Понял. Важно учитывать: вклады с возможностью снятия обычно имеют сниженную ставку. Давайте сравним варианты.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Объяснение trade-off между гибкостью и доходностью'],
                    ],
                ],
                [
                    'id' => 'skip_tradeoff',
                    'text' => 'Хорошо, пополнение будет. Какой у вас примерный доход?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'correctness', 'reason' => 'Пропуск объяснения условий'],
                    ],
                ],
            ],
            'next_stage' => [
                'explain_tradeoff' => 'client_capitalization',
                'skip_tradeoff' => 'client_capitalization',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 4. Объяснение капитализации
        |--------------------------------------------------------------------------
        */

        'client_capitalization' => [
            'client_message' => 'А что такое капитализация процентов? Это выгоднее?',
            'user_options' => [
                [
                    'id' => 'explain_detailed',
                    'text' => 'Капитализация — это когда начисленные проценты добавляются к сумме вклада и в следующем периоде проценты начисляются на бо́льшую сумму. Эффект сложного процента. При ежемесячной капитализации доход немного выше.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Понятное объяснение сложной финансовой концепции'],
                    ],
                ],
                [
                    'id' => 'explain_brief',
                    'text' => 'Да, это когда проценты начисляются на проценты. Немного выгоднее.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Краткое объяснение без деталей'],
                    ],
                ],
            ],
            'next_stage' => [
                'explain_detailed' => 'client_income',
                'explain_brief' => 'client_income',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 5. Доход клиента (для оценки пополнения)
        |--------------------------------------------------------------------------
        */

        'client_income' => [
            'client_message' => 'Примерно 90 тысяч на руки.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'income', 'value' => 90000],
            ],
            'user_options' => [
                [
                    'id' => 'ask_expenses',
                    'text' => 'Хорошо. Какая сумма уходит на обязательные расходы ежемесячно? Это поможет понять комфортную сумму пополнения.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Запрос расходов для оценки возможности пополнения'],
                    ],
                ],
                [
                    'id' => 'skip_expenses',
                    'text' => '90 тысяч — зафиксировал. 20–30 тысяч на пополнение реально. Давайте посчитаем.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'correctness', 'reason' => 'Пропуск оценки расходов'],
                        ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 50000],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_expenses' => 'client_expenses',
                'skip_expenses' => 'deposit_calculation',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 6. Расходы
        |--------------------------------------------------------------------------
        */

        'client_expenses' => [
            'client_message' => 'Примерно 50 тысяч — аренда, продукты, коммуналка.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 50000],
            ],
            'user_options' => [
                [
                    'id' => 'assess_capacity',
                    'text' => 'Значит свободных около 40 тысяч. Пополнение 20–30 тысяч комфортно. Давайте рассчитаю доходность по вкладу.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Оценка финансовой возможности клиента'],
                    ],
                ],
                [
                    'id' => 'skip_to_calc',
                    'text' => 'Понял. Считаю.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'correctness', 'reason' => 'Переход к расчёту без оценки'],
                    ],
                ],
            ],
            'next_stage' => [
                'assess_capacity' => 'deposit_calculation',
                'skip_to_calc' => 'deposit_calculation',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 7. Расчёт вклада
        |--------------------------------------------------------------------------
        */

        'deposit_calculation' => [
            'client_message' => 'Да, посчитайте — интересно посмотреть, что получится.',
            'user_options' => [
                [
                    'id' => 'run_calculation',
                    'text' => 'Рассчитываю доходность вклада с учётом капитализации.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Корректный переход к расчёту'],
                        ['type' => 'calculate_deposit'],
                    ],
                ],
                [
                    'id' => 'run_calculation_approx',
                    'text' => 'Примерно прикину, без точных цифр.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'correctness', 'reason' => 'Неточный расчёт'],
                        ['type' => 'calculate_deposit'],
                    ],
                ],
            ],
            'next_stage' => [
                'run_calculation' => 'deposit_result',
                'run_calculation_approx' => 'deposit_result',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 8. Результат расчёта → предложение
        |--------------------------------------------------------------------------
        */

        'deposit_result' => [
            'client_message' => 'И что вышло?',
            'show_calculations' => true,
            'user_options' => [
                [
                    'id' => 'present_balanced',
                    'text' => 'Вот результат. Обратите внимание на итоговую сумму и доход за весь период. При ежемесячном пополнении 25 тысяч итоговая сумма будет ещё выше.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'correctness', 'reason' => 'Подробная презентация с учётом пополнения'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'medium'],
                    ],
                ],
                [
                    'id' => 'present_upsell',
                    'text' => 'Результат хороший, но если положите миллион — доход будет значительно больше. Может, найдёте дополнительные средства?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Навязывание увеличения суммы'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'high'],
                    ],
                ],
            ],
            'next_stage' => [
                'present_balanced' => 'client_questions',
                'present_upsell' => 'client_concern',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 9. Вопросы клиента
        |--------------------------------------------------------------------------
        */

        'client_questions' => [
            'client_message' => 'А что будет, если я захочу снять деньги досрочно?',
            'user_options' => [
                [
                    'id' => 'explain_early_withdrawal',
                    'text' => 'При досрочном расторжении проценты пересчитываются по ставке «до востребования» — обычно 0,01%. То есть практически весь доход теряется. Поэтому важно быть уверенным в сроке.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Честное информирование о рисках досрочного снятия'],
                    ],
                ],
                [
                    'id' => 'downplay_risk',
                    'text' => 'Можно снять в любое время, просто проценты будут чуть меньше.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Преуменьшение рисков досрочного расторжения'],
                    ],
                ],
            ],
            'next_stage' => [
                'explain_early_withdrawal' => 'client_satisfied',
                'downplay_risk' => 'client_satisfied',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 9а. Тревога клиента (навязывание суммы)
        |--------------------------------------------------------------------------
        */

        'client_concern' => [
            'client_message' => 'Нет, у меня именно 500 тысяч свободных. Больше не могу.',
            'user_options' => [
                [
                    'id' => 'backtrack',
                    'text' => 'Конечно, 500 тысяч — отличная сумма для старта. Давайте оформим на эту сумму.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Корректировка по запросу клиента'],
                    ],
                ],
                [
                    'id' => 'insist',
                    'text' => 'Ну, может кредит взять и на вклад положить? Разница в ставках...',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -20, 'category' => 'compliance', 'reason' => 'Абсурдное предложение кредита для вклада'],
                    ],
                ],
            ],
            'next_stage' => [
                'backtrack' => 'client_questions',
                'insist' => 'client_questions',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 10. Клиент доволен → оформление
        |--------------------------------------------------------------------------
        */

        'client_satisfied' => [
            'client_message' => 'Хорошо, всё понятно. Давайте оформим вклад.',
            'user_options' => [
                [
                    'id' => 'ask_passport',
                    'text' => 'Отлично. Для оформления мне нужен ваш паспорт. Также рекомендую привязать вклад к дебетовой карте для удобного пополнения.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'compliance', 'reason' => 'Корректный запрос документов + полезная рекомендация'],
                    ],
                ],
                [
                    'id' => 'skip_docs',
                    'text' => 'Оформлю без документов, потом занесёте.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -15, 'category' => 'compliance', 'reason' => 'Оформление без верификации личности'],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_passport' => 'collect_passport',
                'skip_docs' => 'completion_no_docs',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 11. Сбор документов
        |--------------------------------------------------------------------------
        */

        'collect_passport' => [
            'client_message' => 'Вот паспорт.',
            'user_options' => [
                [
                    'id' => 'finalize_proper',
                    'text' => 'Спасибо. Вот договор вклада — обратите внимание на ставку, условия капитализации и штрафы за досрочное расторжение.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Разъяснение ключевых условий договора'],
                    ],
                ],
                [
                    'id' => 'finalize_quick',
                    'text' => 'Всё оформлено. Подписывайте.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'compliance', 'reason' => 'Оформление без разъяснения условий договора'],
                    ],
                ],
            ],
            'next_stage' => [
                'finalize_proper' => 'completion',
                'finalize_quick' => 'completion',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 12. Успешное завершение
        |--------------------------------------------------------------------------
        */

        'completion' => [
            'client_message' => 'Спасибо за подробную консультацию! Теперь понимаю, как работает вклад. До свидания!',
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 12а. Завершение без документов
        |--------------------------------------------------------------------------
        */

        'completion_no_docs' => [
            'client_message' => 'Ладно, принесу паспорт завтра.',
            'on_enter_actions' => [
                ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Оформление вклада без верификации личности'],
            ],
            'is_final' => true,
        ],

    ],
];
