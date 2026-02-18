<?php

declare(strict_types=1);

return [
    'stages' => [

        /*
        |--------------------------------------------------------------------------
        | 1. Старт
        |--------------------------------------------------------------------------
        */

        'greeting' => [
            'client_message' => 'Здравствуйте. Хочу оформить кредитную карту.',
            'user_options' => [
                [
                    'id' => 'clarify_goal',
                    'text' => 'Для каких целей планируете использовать карту?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Уточнение цели использования карты перед оформлением',
                        ],
                    ],
                ],
                [
                    'id' => 'sell_immediately',
                    'text' => 'Можем оформить прямо сейчас. Нужен только паспорт.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => -10,
                            'reason' => 'Агрессивная продажа без выяснения потребности клиента',
                        ],
                        ['type' => 'update_client_data', 'field' => 'aggressive_sale', 'value' => true],
                    ],
                ],
            ],
            'next_stage' => [
                'clarify_goal' => 'client_goal',
                'sell_immediately' => 'client_skeptic',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 2. Потребность
        |--------------------------------------------------------------------------
        */

        'client_goal' => [
            'client_message' => 'Скорее как подушку безопасности.',
            'user_options' => [
                [
                    'id' => 'ask_income',
                    'text' => 'Подскажите ваш официальный доход в месяц?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Запрос дохода для оценки платежеспособности',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_income_detailed',
                    'text' => 'Чтобы подобрать безопасный лимит, назовите, пожалуйста, ежемесячный официальный доход.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Корректное уточнение дохода с объяснением цели вопроса',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_income_quick',
                    'text' => 'Окей, доход в месяц?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 5,
                            'reason' => 'Доход запрошен, но формулировка менее профессиональна',
                        ],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_income' => 'client_income',
                'ask_income_detailed' => 'client_income',
                'ask_income_quick' => 'client_income',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 3. Доход
        |--------------------------------------------------------------------------
        */

        'client_income' => [
            'client_message' => '80 000 рублей.',
            'required_data' => ['income'],
            'user_options' => [
                [
                    'id' => 'ask_expenses',
                    'text' => 'Сколько составляют ваши ежемесячные расходы?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Запрос расходов для расчёта долговой нагрузки',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_expenses_detailed',
                    'text' => 'Спасибо. Уточните, пожалуйста, регулярные ежемесячные расходы: аренда, кредиты, обязательные платежи.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Профессиональная детализация структуры расходов',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_expenses_brief',
                    'text' => 'А по расходам сколько в среднем уходит?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 8,
                            'reason' => 'Расходы запрошены, но без достаточной структурности',
                        ],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_expenses' => 'client_expenses',
                'ask_expenses_detailed' => 'client_expenses',
                'ask_expenses_brief' => 'client_expenses',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 4. Расходы
        |--------------------------------------------------------------------------
        */

        'client_expenses' => [
            'client_message' => '60 000 рублей.',
            'required_data' => ['expenses'],
            'user_options' => [
                [
                    'id' => 'ask_debts',
                    'text' => 'Есть ли действующие кредиты?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Запрос информации о текущей кредитной нагрузке',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_debts_detailed',
                    'text' => 'Есть ли сейчас действующие кредиты или рассрочки? Если да — какой ежемесячный платеж?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Уточнение действующих обязательств и платежа по ним',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_debts_quick',
                    'text' => 'Кредиты сейчас есть?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 7,
                            'reason' => 'Факт кредитов уточнен, но без детализации платежей',
                        ],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_debts' => 'client_debts',
                'ask_debts_detailed' => 'client_debts',
                'ask_debts_quick' => 'client_debts',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 5. Долги
        |--------------------------------------------------------------------------
        */

        'client_debts' => [
            'client_message' => 'Да, плачу 7 000 рублей по кредиту.',
            'user_options' => [
                [
                    'id' => 'ask_history',
                    'text' => 'Были ли просрочки по кредитам?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Запрос кредитной истории перед скорингом',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_history_detailed',
                    'text' => 'Понимаю. Подскажите, были ли за последние 12 месяцев просрочки по текущим или прошлым кредитам?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Уточнение кредитной дисциплины на релевантном горизонте',
                        ],
                    ],
                ],
                [
                    'id' => 'ask_history_quick',
                    'text' => 'С просрочками как ситуация?',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 7,
                            'reason' => 'Кредитная история уточнена, но вопрос сформулирован менее делово',
                        ],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_history' => 'client_history',
                'ask_history_detailed' => 'client_history',
                'ask_history_quick' => 'client_history',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 6. История
        |--------------------------------------------------------------------------
        */

        'client_history' => [
            'client_message' => 'Нет, просрочек не было.',
            'user_options' => [
                [
                    'id' => 'run_scoring',
                    'text' => 'Проведу скоринговую оценку.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Корректный переход к скорингу после сбора ключевых данных',
                        ],
                        ['type' => 'calculate_scoring'],
                        ['type' => 'calculate_credit'],
                        [
                            'type' => 'check_condition',
                            'condition' => 'dti > 0.5',
                            'true_stage' => 'auto_decline_dti',
                        ],
                        [
                            'type' => 'check_condition',
                            'condition' => 'credit_history == "bad"',
                            'true_stage' => 'bad_history_decline',
                        ],
                    ],
                ],
                [
                    'id' => 'run_scoring_explain',
                    'text' => 'Отлично, запущу скоринг и сразу покажу оптимальные условия.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 10,
                            'reason' => 'Профессиональная коммуникация при запуске скоринга',
                        ],
                        ['type' => 'calculate_scoring'],
                        ['type' => 'calculate_credit'],
                        [
                            'type' => 'check_condition',
                            'condition' => 'dti > 0.5',
                            'true_stage' => 'auto_decline_dti',
                        ],
                        [
                            'type' => 'check_condition',
                            'condition' => 'credit_history == "bad"',
                            'true_stage' => 'bad_history_decline',
                        ],
                    ],
                ],
                [
                    'id' => 'run_scoring_quick',
                    'text' => 'Секунду, считаю.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 6,
                            'reason' => 'Скоринг выполнен, но коммуникация с клиентом минимальна',
                        ],
                        ['type' => 'calculate_scoring'],
                        ['type' => 'calculate_credit'],
                        [
                            'type' => 'check_condition',
                            'condition' => 'dti > 0.5',
                            'true_stage' => 'auto_decline_dti',
                        ],
                        [
                            'type' => 'check_condition',
                            'condition' => 'credit_history == "bad"',
                            'true_stage' => 'bad_history_decline',
                        ],
                    ],
                ],
            ],
            'next_stage' => [
                'run_scoring' => 'offer_stage',
                'run_scoring_explain' => 'offer_stage',
                'run_scoring_quick' => 'offer_stage',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 7. Авто-отказ по DTI
        |--------------------------------------------------------------------------
        */

        'auto_decline_dti' => [
            'client_message' => 'К сожалению, по расчетам долговая нагрузка превышает допустимый уровень. Банк не может одобрить заявку.',
            'actions' => [
                [
                    'type' => 'add_score_points',
                    'points' => -20,
                    'reason' => 'Попытка выдачи кредита при DTI выше 50% — нарушение политики банка',
                ],
                ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'high'],
            ],
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 8. Отказ по плохой истории
        |--------------------------------------------------------------------------
        */

        'bad_history_decline' => [
            'client_message' => 'К сожалению, по данным кредитной истории одобрение невозможно.',
            'actions' => [
                [
                    'type' => 'add_score_points',
                    'points' => -15,
                    'reason' => 'Попытка выдачи кредита клиенту с неудовлетворительной кредитной историей',
                ],
                ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'very_high'],
            ],
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 9. Предложение
        |--------------------------------------------------------------------------
        */

        'offer_stage' => [
            'client_message' => 'Какие условия вы можете предложить?',
            'show_calculations' => true,
            'user_options' => [
                [
                    'id' => 'conservative_offer',
                    'text' => 'Предлагаем лимит 250 000 рублей под 18% годовых.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 20,
                            'reason' => 'Предложение консервативного лимита с управляемой нагрузкой на клиента',
                        ],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'medium'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'low'],
                    ],
                ],
                [
                    'id' => 'aggressive_offer',
                    'text' => 'Можем одобрить 400 000 рублей под 22% годовых.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => -15,
                            'reason' => 'Предложение завышенного лимита с высоким риском дефолта (DTI > 40%)',
                        ],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'high'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'medium'],
                    ],
                ],
            ],
            'next_stage' => [
                'conservative_offer' => 'client_decision',
                'aggressive_offer' => 'risk_warning',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 10. Предупреждение о риске
        |--------------------------------------------------------------------------
        */

        'risk_warning' => [
            'client_message' => 'С таким лимитом платеж будет выше. Вы уверены?',
            'user_options' => [
                [
                    'id' => 'explain_risk',
                    'text' => 'Да, нагрузка вырастет до 45% дохода. Это повышает риск просрочки.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 20,
                            'reason' => 'Честное информирование клиента о рисках завышенного лимита',
                        ],
                    ],
                ],
                [
                    'id' => 'ignore_risk',
                    'text' => 'Это стандартная практика, переживать не стоит.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => -20,
                            'reason' => 'Скрытие рисков от клиента — нарушение стандартов консультирования',
                        ],
                        ['type' => 'update_client_data', 'field' => 'future_default_flag', 'value' => true],
                    ],
                ],
            ],
            'next_stage' => [
                'explain_risk' => 'client_decision',
                'ignore_risk' => 'future_default_event',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 11. Будущий дефолт (NPL событие)
        |--------------------------------------------------------------------------
        */

        'future_default_event' => [
            'client_message' => 'Через 6 месяцев клиент допустил просрочку 90+ дней. Кредит переведен в NPL.',
            'actions' => [
                [
                    'type' => 'add_score_points',
                    'points' => -50,
                    'reason' => 'Кредит перешёл в NPL: клиент не справился с нагрузкой, которую вы не предупредили',
                ],
                ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'negative'],
            ],
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 12. Решение клиента
        |--------------------------------------------------------------------------
        */

        'client_decision' => [
            'client_message' => 'Хорошо, оформляем.',
            'user_options' => [
                [
                    'id' => 'finalize',
                    'text' => 'Подготовлю заявку и документы.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 30,
                            'reason' => 'Успешное завершение консультации и оформление продукта',
                        ],
                    ],
                ],
                [
                    'id' => 'finalize_detailed',
                    'text' => 'Отлично, фиксирую параметры, подготовлю заявку и передам документы на подписание.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 30,
                            'reason' => 'Четкое завершение сделки с пояснением следующих шагов',
                        ],
                    ],
                ],
                [
                    'id' => 'finalize_short',
                    'text' => 'Супер, тогда оформляем.',
                    'actions' => [
                        [
                            'type' => 'add_score_points',
                            'points' => 20,
                            'reason' => 'Сделка закрыта, но финальная коммуникация менее структурна',
                        ],
                    ],
                ],
            ],
            'next_stage' => [
                'finalize' => 'completion',
                'finalize_detailed' => 'completion',
                'finalize_short' => 'completion',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 13. Финал
        |--------------------------------------------------------------------------
        */

        'completion' => [
            'client_message' => 'Спасибо за консультацию.',
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Скепсис
        |--------------------------------------------------------------------------
        */

        'client_skeptic' => [
            'client_message' => 'Мне важно понять условия, а не просто оформить.',
            'is_final' => true,
        ],

    ],
];
