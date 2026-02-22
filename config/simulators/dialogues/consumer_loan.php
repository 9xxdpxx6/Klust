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
            'client_message' => 'Здравствуйте! Хочу взять потребительский кредит на покупку автомобиля.',
            'user_options' => [
                [
                    'id' => 'greet_warm',
                    'text' => 'Добрый день! Присаживайтесь. Какой автомобиль рассматриваете и примерная сумма?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Вежливое приветствие и уточнение цели'],
                    ],
                ],
                [
                    'id' => 'greet_push',
                    'text' => 'Добрый день! У нас сейчас акция — сниженная ставка. Давайте быстро оформим!',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -5, 'category' => 'service_quality', 'reason' => 'Давление без выяснения потребности'],
                    ],
                ],
            ],
            'next_stage' => [
                'greet_warm' => 'client_need',
                'greet_push' => 'client_pushback',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 1а. Реакция клиента на давление
        |--------------------------------------------------------------------------
        */

        'client_pushback' => [
            'client_message' => 'Не торопите, я хочу сначала разобраться в условиях.',
            'user_options' => [
                [
                    'id' => 'recover_polite',
                    'text' => 'Конечно, извините. Давайте обсудим ваши пожелания — расскажите подробнее.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Исправление ситуации'],
                    ],
                ],
                [
                    'id' => 'recover_dry',
                    'text' => 'Хорошо, рассказывайте.',
                    'actions' => [],
                ],
            ],
            'next_stage' => [
                'recover_polite' => 'client_need',
                'recover_dry' => 'client_need',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 2. Потребность клиента
        |--------------------------------------------------------------------------
        */

        'client_need' => [
            'client_message' => 'Присмотрел машину за 1,2 миллиона. Хочу взять в кредит примерно миллион, 200 тысяч внесу сразу.',
            'user_options' => [
                [
                    'id' => 'need_structured',
                    'text' => 'Хороший план. Чтобы подобрать условия, задам несколько вопросов. Какой у вас ежемесячный доход?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Структурированный подход'],
                    ],
                ],
                [
                    'id' => 'need_quick',
                    'text' => 'Миллион — нормальная сумма. Сколько получаете?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 7, 'category' => 'correctness', 'reason' => 'Быстрый переход к делу'],
                    ],
                ],
                [
                    'id' => 'need_promise',
                    'text' => 'Миллион точно одобрим, не переживайте. Давайте оформлять.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Необоснованное обещание одобрения'],
                    ],
                ],
            ],
            'next_stage' => [
                'need_structured' => 'client_income',
                'need_quick' => 'client_income',
                'need_promise' => 'client_income',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 3. Доход
        |--------------------------------------------------------------------------
        */

        'client_income' => [
            'client_message' => '65 тысяч рублей чистыми.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'income', 'value' => 65000],
            ],
            'user_options' => [
                [
                    'id' => 'ask_expenses',
                    'text' => 'Понял. Какая сумма уходит ежемесячно на обязательные расходы?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Запрос расходов'],
                    ],
                ],
                [
                    'id' => 'skip_to_debts',
                    'text' => '65 тысяч, зафиксировал. Есть другие кредиты?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'correctness', 'reason' => 'Пропуск вопроса о расходах'],
                        ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 30000],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_expenses' => 'client_expenses',
                'skip_to_debts' => 'client_debts',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 4. Расходы
        |--------------------------------------------------------------------------
        */

        'client_expenses' => [
            'client_message' => 'Примерно 35 тысяч — коммуналка, еда, бензин.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 35000],
            ],
            'user_options' => [
                [
                    'id' => 'ask_debts',
                    'text' => 'Понял. Есть действующие кредиты или рассрочки?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Полная оценка долговой нагрузки'],
                    ],
                ],
                [
                    'id' => 'ask_debts_empathy',
                    'text' => 'Нормальный уровень. Уточню — другие кредитные обязательства есть?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 8, 'category' => 'service_quality', 'reason' => 'Позитивная обратная связь'],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_debts' => 'client_debts',
                'ask_debts_empathy' => 'client_debts',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 5. Действующие долги
        |--------------------------------------------------------------------------
        */

        'client_debts' => [
            'client_message' => 'Нет, ничего нет. Всё закрыл в прошлом году.',
            'user_options' => [
                [
                    'id' => 'ask_history',
                    'text' => 'Отлично. Были ли когда-нибудь просрочки по предыдущим кредитам?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Проверка кредитной дисциплины'],
                    ],
                ],
                [
                    'id' => 'ask_history_direct',
                    'text' => 'Хорошо. Просрочки были?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'correctness', 'reason' => 'Прямой вопрос'],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_history' => 'client_history',
                'ask_history_direct' => 'client_history',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 6. Кредитная история
        |--------------------------------------------------------------------------
        */

        'client_history' => [
            'client_message' => 'Нет, всё закрывал вовремя.',
            'user_options' => [
                [
                    'id' => 'check_bki',
                    'text' => 'Хорошо. Проверю кредитную историю в НБКИ — это стандартная процедура.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Обязательная проверка КИ'],
                    ],
                ],
                [
                    'id' => 'trust_word',
                    'text' => 'Верю. Сколько вам полных лет?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Пропуск обязательной проверки КИ'],
                        ['type' => 'update_client_data', 'field' => 'credit_history', 'value' => 'good'],
                    ],
                ],
            ],
            'next_stage' => [
                'check_bki' => 'bki_check',
                'trust_word' => 'client_age',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 7. Проверка БКИ
        |--------------------------------------------------------------------------
        */

        'bki_check' => [
            'on_enter_actions' => [
                ['type' => 'show_message', 'role' => 'system', 'message' => '🔍 Запрос в НБКИ...'],
                ['type' => 'show_message', 'role' => 'system', 'message' => '📋 Получение кредитного отчёта...'],
                ['type' => 'show_message', 'role' => 'system', 'message' => '✅ Отчёт получен. Кредитная история положительная.'],
                ['type' => 'update_client_data', 'field' => 'credit_history', 'value' => 'good'],
            ],
            'client_message' => 'Всё в порядке?',
            'user_options' => [
                [
                    'id' => 'bki_reassure',
                    'text' => 'Да, история хорошая. Осталось уточнить возраст для расчёта.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Информирование о результатах'],
                    ],
                ],
                [
                    'id' => 'bki_brief',
                    'text' => 'Всё чисто. Возраст?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 2, 'category' => 'service_quality', 'reason' => 'Краткое подтверждение'],
                    ],
                ],
            ],
            'next_stage' => [
                'bki_reassure' => 'client_age',
                'bki_brief' => 'client_age',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 8. Возраст
        |--------------------------------------------------------------------------
        */

        'client_age' => [
            'client_message' => '28 лет.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'age', 'value' => 28],
            ],
            'user_options' => [
                [
                    'id' => 'run_scoring',
                    'text' => 'Спасибо, все данные собраны. Провожу скоринговую оценку.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Корректный переход к скорингу'],
                        ['type' => 'calculate_scoring'],
                        ['type' => 'calculate_credit'],
                    ],
                ],
                [
                    'id' => 'run_scoring_comment',
                    'text' => '28 лет — самый активный возраст для кредитов. Минуту, считаю.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'service_quality', 'reason' => 'Неуместный комментарий о возрасте'],
                        ['type' => 'calculate_scoring'],
                        ['type' => 'calculate_credit'],
                    ],
                ],
            ],
            'next_stage' => [
                'run_scoring' => 'scoring_result',
                'run_scoring_comment' => 'scoring_result',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 9. Результат скоринга
        |--------------------------------------------------------------------------
        */

        'scoring_result' => [
            'client_message' => 'Ну как, одобрили?',
            'show_calculations' => true,
            'user_options' => [
                [
                    'id' => 'present_balanced',
                    'text' => 'Да, одобрено. Вот условия — обратите внимание на ежемесячный платёж и переплату за весь срок.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 20, 'category' => 'correctness', 'reason' => 'Сбалансированная презентация'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'medium'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'low'],
                    ],
                ],
                [
                    'id' => 'present_upsell',
                    'text' => 'Одобрено! Кстати, можем одобрить до 1,5 миллиона — может, возьмёте машину подороже?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -15, 'category' => 'compliance', 'reason' => 'Навязывание завышенной суммы'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'high'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'medium'],
                    ],
                ],
            ],
            'next_stage' => [
                'present_balanced' => 'client_satisfied',
                'present_upsell' => 'client_concern',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 10. Тревога клиента
        |--------------------------------------------------------------------------
        */

        'client_concern' => [
            'client_message' => 'Нет, мне достаточно миллиона. Я уже выбрал машину.',
            'user_options' => [
                [
                    'id' => 'backtrack',
                    'text' => 'Понял, извините. Оставляем миллион — вот итоговые условия.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Корректировка предложения'],
                    ],
                ],
                [
                    'id' => 'insist',
                    'text' => 'Ну смотрите, запас не помешает. Мало ли что.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -20, 'category' => 'compliance', 'reason' => 'Настаивание на увеличении суммы'],
                        ['type' => 'update_client_data', 'field' => 'future_default_flag', 'value' => true],
                    ],
                ],
            ],
            'next_stage' => [
                'backtrack' => 'client_satisfied',
                'insist' => 'client_reluctant',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 10а. Клиент нехотя соглашается
        |--------------------------------------------------------------------------
        */

        'client_reluctant' => [
            'client_message' => 'Ладно, пусть будет полтора... Давайте оформлять.',
            'user_options' => [
                [
                    'id' => 'proceed_risky',
                    'text' => 'Отлично! Давайте ваш паспорт.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -5, 'category' => 'compliance', 'reason' => 'Оформление при дискомфорте клиента'],
                    ],
                ],
                [
                    'id' => 'reconsider',
                    'text' => 'Знаете, давайте лучше миллион. Платёж будет комфортнее.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'service_quality', 'reason' => 'Ответственное решение'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'low'],
                        ['type' => 'update_client_data', 'field' => 'future_default_flag', 'value' => false],
                    ],
                ],
            ],
            'next_stage' => [
                'proceed_risky' => 'collect_passport_risky',
                'reconsider' => 'client_satisfied',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 11. Клиент доволен
        |--------------------------------------------------------------------------
        */

        'client_satisfied' => [
            'client_message' => 'Отлично, условия устраивают. Оформляем.',
            'user_options' => [
                [
                    'id' => 'ask_passport',
                    'text' => 'Хорошо. Для оформления мне нужен ваш паспорт и справка о доходах.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'compliance', 'reason' => 'Корректный запрос документов'],
                    ],
                ],
                [
                    'id' => 'skip_docs',
                    'text' => 'Оформляю. Справку можете потом донести.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -15, 'category' => 'compliance', 'reason' => 'Оформление без полного пакета документов'],
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
        | 12. Сбор документов
        |--------------------------------------------------------------------------
        */

        'collect_passport' => [
            'client_message' => 'Вот паспорт и справка.',
            'user_options' => [
                [
                    'id' => 'finalize_proper',
                    'text' => 'Спасибо. Вот договор — пожалуйста, внимательно ознакомьтесь перед подписанием.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Предоставление договора для ознакомления'],
                    ],
                ],
                [
                    'id' => 'finalize_quick',
                    'text' => 'Всё готово. Подписывайте здесь.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'compliance', 'reason' => 'Оформление без разъяснения условий'],
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
        | 12а. Паспорт (опасный путь)
        |--------------------------------------------------------------------------
        */

        'collect_passport_risky' => [
            'client_message' => 'Вот паспорт.',
            'user_options' => [
                [
                    'id' => 'finalize_risky',
                    'text' => 'Спасибо, оформляю. Подписывайте.',
                    'actions' => [],
                ],
            ],
            'next_stage' => [
                'finalize_risky' => 'future_default_event',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 13. Успешное завершение
        |--------------------------------------------------------------------------
        */

        'completion' => [
            'client_message' => 'Спасибо за помощь! Пойду за машиной. Всего доброго!',
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 13а. Завершение без документов
        |--------------------------------------------------------------------------
        */

        'completion_no_docs' => [
            'client_message' => 'Хорошо, донесу справку на неделе.',
            'on_enter_actions' => [
                ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Выдача кредита без полного пакета документов'],
            ],
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 14. NPL-событие
        |--------------------------------------------------------------------------
        */

        'future_default_event' => [
            'client_message' => '⚠️ Через 8 месяцев: клиент не справляется с платежами по завышенному кредиту. Просрочка 90+ дней. Кредит в категории NPL.',
            'on_enter_actions' => [
                ['type' => 'add_score_points', 'points' => -30, 'category' => 'correctness', 'reason' => 'Кредит перешёл в NPL из-за завышенной суммы'],
                ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'negative'],
            ],
            'is_final' => true,
        ],

    ],
];
