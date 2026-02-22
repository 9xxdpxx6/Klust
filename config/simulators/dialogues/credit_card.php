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
            'client_message' => 'Добрый день! Я бы хотел узнать насчёт кредитной карты.',
            'user_options' => [
                [
                    'id' => 'greet_warm',
                    'text' => 'Здравствуйте! Конечно, присаживайтесь. Расскажите, что именно вас интересует?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Вежливое приветствие и открытый вопрос'],
                    ],
                ],
                [
                    'id' => 'greet_sell',
                    'text' => 'Добрый день! У нас как раз акция — оформление за 5 минут. Давайте сразу заявку?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -5, 'category' => 'service_quality', 'reason' => 'Агрессивная продажа без выяснения потребности'],
                    ],
                ],
            ],
            'next_stage' => [
                'greet_warm' => 'client_need',
                'greet_sell' => 'client_pushback',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 1а. Реакция клиента на давление
        |--------------------------------------------------------------------------
        */

        'client_pushback' => [
            'client_message' => 'Подождите-подождите. Я хотел сначала разобраться в условиях, а не оформлять вслепую.',
            'user_options' => [
                [
                    'id' => 'recover_polite',
                    'text' => 'Конечно, извините за спешку. Давайте я подробно всё расскажу. Для каких целей вам нужна карта?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Исправление ситуации после агрессивного старта'],
                    ],
                ],
                [
                    'id' => 'recover_dry',
                    'text' => 'Хорошо, расскажите тогда, что вас интересует.',
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
            'client_message' => 'Хочу карту как подушку безопасности — на непредвиденные расходы. Лимит тысяч 200–300 был бы идеально.',
            'user_options' => [
                [
                    'id' => 'need_structured',
                    'text' => 'Разумный подход. Чтобы подобрать оптимальные условия, задам несколько вопросов. Какой у вас примерный ежемесячный доход?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Структурированный подход к выявлению потребности'],
                    ],
                ],
                [
                    'id' => 'need_casual',
                    'text' => 'Понял, 200–300 — реальный диапазон. Давайте прикинем. Сколько получаете на руки?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 7, 'category' => 'correctness', 'reason' => 'Быстрый переход к делу'],
                    ],
                ],
                [
                    'id' => 'need_promise',
                    'text' => '200–300 тысяч — без проблем, наверняка одобрим. Но формально нужно пройти проверку.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Необоснованное обещание одобрения до проверки данных'],
                    ],
                ],
            ],
            'next_stage' => [
                'need_structured' => 'client_income',
                'need_casual' => 'client_income',
                'need_promise' => 'client_income',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 3. Доход
        |--------------------------------------------------------------------------
        */

        'client_income' => [
            'client_message' => 'Около 80 тысяч рублей, это чистыми на руки.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'income', 'value' => 80000],
            ],
            'user_options' => [
                [
                    'id' => 'ask_expenses',
                    'text' => 'Хорошо. А какая примерно сумма уходит ежемесячно на обязательные платежи — коммуналка, продукты, транспорт?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Запрос расходов для оценки платёжеспособности'],
                    ],
                ],
                [
                    'id' => 'skip_to_debts',
                    'text' => '80 тысяч, зафиксировал. Есть действующие кредиты или рассрочки?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'correctness', 'reason' => 'Пропуск вопроса о расходах — неполная оценка нагрузки'],
                        ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 40000],
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
            'client_message' => 'Где-то 45 тысяч в месяц, если всё сложить.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 45000],
            ],
            'user_options' => [
                [
                    'id' => 'ask_debts',
                    'text' => 'Понял. Есть ли сейчас действующие кредиты или рассрочки?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Полная оценка долговой нагрузки'],
                    ],
                ],
                [
                    'id' => 'ask_debts_empathy',
                    'text' => 'Нормальное соотношение. Сразу уточню — других кредитных обязательств нет?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 8, 'category' => 'service_quality', 'reason' => 'Позитивная обратная связь + запрос долговой нагрузки'],
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
            'client_message' => 'Есть потребительский кредит, плачу по 7 000 в месяц. Осталось полгода до закрытия.',
            'user_options' => [
                [
                    'id' => 'ask_history',
                    'text' => 'Хорошо, значит скоро нагрузка снизится. Были ли когда-нибудь просрочки по платежам?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Эмпатичный переход к проверке кредитной дисциплины'],
                    ],
                ],
                [
                    'id' => 'ask_history_direct',
                    'text' => '7 000 — учту. Просрочки по кредитам были?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'correctness', 'reason' => 'Прямой вопрос без контекста'],
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
        | 6. Кредитная дисциплина (самоотчёт клиента)
        |--------------------------------------------------------------------------
        */

        'client_history' => [
            'client_message' => 'Нет, всё плачу вовремя. Ни разу не задерживал.',
            'user_options' => [
                [
                    'id' => 'check_bki',
                    'text' => 'Отлично. Сейчас проверю вашу кредитную историю в НБКИ — это стандартная процедура.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Проверка кредитной истории через БКИ — обязательная процедура'],
                    ],
                ],
                [
                    'id' => 'trust_word',
                    'text' => 'Хорошо, верю на слово. Для анкеты — сколько вам полных лет?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Пропуск обязательной проверки кредитной истории в БКИ'],
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
        | 7. Симуляция проверки БКИ (НБКИ)
        |--------------------------------------------------------------------------
        */

        'bki_check' => [
            'on_enter_actions' => [
                ['type' => 'show_message', 'role' => 'system', 'message' => '🔍 Запрос в НБКИ (Национальное бюро кредитных историй)...'],
                ['type' => 'show_message', 'role' => 'system', 'message' => '📋 Получение кредитного отчёта по субъекту...'],
                ['type' => 'show_message', 'role' => 'system', 'message' => '✅ Отчёт НБКИ получен. Кредитная история положительная. Просрочек за последние 12 мес. не выявлено.'],
                ['type' => 'update_client_data', 'field' => 'credit_history', 'value' => 'good'],
            ],
            'client_message' => 'Надеюсь, всё в порядке?',
            'user_options' => [
                [
                    'id' => 'bki_reassure',
                    'text' => 'Да, всё отлично — история чистая. Осталось уточнить возраст для скоринговой модели.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Информирование клиента о результатах проверки'],
                    ],
                ],
                [
                    'id' => 'bki_brief',
                    'text' => 'Всё чисто. Сколько вам полных лет?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 2, 'category' => 'service_quality', 'reason' => 'Краткое подтверждение без деталей'],
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
        | 8. Возраст клиента
        |--------------------------------------------------------------------------
        */

        'client_age' => [
            'client_message' => 'Мне 32 года.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'age', 'value' => 32],
            ],
            'user_options' => [
                [
                    'id' => 'run_scoring',
                    'text' => 'Спасибо, все данные для расчёта есть. Провожу скоринговую оценку — это займёт пару секунд.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Корректный переход к скорингу после сбора данных'],
                        ['type' => 'calculate_scoring'],
                        ['type' => 'calculate_credit'],
                    ],
                ],
                [
                    'id' => 'run_scoring_comment',
                    'text' => '32 — отличный возраст для кредитных продуктов. Секунду, всё посчитаю.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'service_quality', 'reason' => 'Неуместный комментарий о возрасте клиента'],
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
        | 9. Результат скоринга → предложение
        |--------------------------------------------------------------------------
        */

        'scoring_result' => [
            'client_message' => 'Ну что, одобрили?',
            'show_calculations' => true,
            'user_options' => [
                [
                    'id' => 'present_conservative',
                    'text' => 'Да, одобрение получено. Предлагаю комфортный лимит под ваш доход — все детали на экране.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 20, 'category' => 'correctness', 'reason' => 'Сбалансированное предложение на основе скоринга'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'medium'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'low'],
                    ],
                ],
                [
                    'id' => 'present_aggressive',
                    'text' => 'Одобрено! Могу предложить повышенный лимит — 400 000 рублей. Ставка чуть выше, но зато запас больше.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -15, 'category' => 'compliance', 'reason' => 'Навязывание завышенного лимита с высоким риском дефолта'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'high'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'medium'],
                    ],
                ],
            ],
            'next_stage' => [
                'present_conservative' => 'client_satisfied',
                'present_aggressive' => 'client_concern',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 10. Тревога клиента (завышенный лимит)
        |--------------------------------------------------------------------------
        */

        'client_concern' => [
            'client_message' => '400 тысяч? Не многовато? Я же говорил — мне карта на непредвиденные расходы, а не для покупок на всю зарплату.',
            'user_options' => [
                [
                    'id' => 'backtrack',
                    'text' => 'Вы правы, извините. Давайте вернёмся к комфортному лимиту, который соответствует вашему запросу.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Корректировка предложения по запросу клиента'],
                    ],
                ],
                [
                    'id' => 'insist',
                    'text' => 'Это стандартный лимит для вашего профиля. Вы не обязаны его весь использовать.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -20, 'category' => 'compliance', 'reason' => 'Навязывание завышенного лимита вопреки желанию клиента'],
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
        | 10а. Клиент нехотя соглашается (опасный путь)
        |--------------------------------------------------------------------------
        */

        'client_reluctant' => [
            'client_message' => 'Ну... ладно, вам виднее. Давайте оформлять.',
            'user_options' => [
                [
                    'id' => 'proceed_risky',
                    'text' => 'Отлично! Мне потребуется ваш паспорт.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -5, 'category' => 'compliance', 'reason' => 'Оформление продукта при явном дискомфорте клиента'],
                    ],
                ],
                [
                    'id' => 'reconsider',
                    'text' => 'Знаете, давайте всё-таки снизим лимит. Лучше комфортнее, чем потом переплачивать.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'service_quality', 'reason' => 'Ответственное решение в пользу клиента'],
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
        | 11. Клиент доволен условиями
        |--------------------------------------------------------------------------
        */

        'client_satisfied' => [
            'client_message' => 'Хорошо, условия устраивают. Давайте оформлять.',
            'user_options' => [
                [
                    'id' => 'ask_passport',
                    'text' => 'Отлично. Для оформления договора мне потребуется ваш паспорт.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'compliance', 'reason' => 'Корректный запрос документов после согласия клиента'],
                    ],
                ],
                [
                    'id' => 'skip_docs',
                    'text' => 'Хорошо, оформляю. Документы можно донести позже.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -15, 'category' => 'compliance', 'reason' => 'Оформление без проверки документов — нарушение процедуры'],
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
        | 12. Сбор документов (паспорт → раскрытие ФИО)
        |--------------------------------------------------------------------------
        */

        'collect_passport' => [
            'client_message' => 'Вот, пожалуйста, мой паспорт.',
            'user_options' => [
                [
                    'id' => 'finalize_proper',
                    'text' => 'Спасибо. Данные проверены, заявка сформирована. Вот договор — пожалуйста, ознакомьтесь с условиями перед подписанием.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Предоставление договора для ознакомления перед подписанием'],
                    ],
                ],
                [
                    'id' => 'finalize_quick',
                    'text' => 'Всё в порядке. Подписывайте здесь и здесь — карту активируем сразу.',
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
        | 12а. Паспорт (опасный путь → NPL)
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
            'client_message' => 'Спасибо за консультацию! Всего доброго.',
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 13а. Завершение без документов
        |--------------------------------------------------------------------------
        */

        'completion_no_docs' => [
            'client_message' => 'Ладно, спасибо. Занесу документы на днях.',
            'on_enter_actions' => [
                ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Выдача кредитного продукта без верификации личности'],
            ],
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 14. NPL-событие (дефолт через 6 месяцев)
        |--------------------------------------------------------------------------
        */

        'future_default_event' => [
            'client_message' => '⚠️ Через 6 месяцев: клиент допустил просрочку свыше 90 дней. Кредит переведён в категорию NPL (проблемная задолженность).',
            'on_enter_actions' => [
                ['type' => 'add_score_points', 'points' => -30, 'category' => 'correctness', 'reason' => 'Кредит перешёл в NPL из-за завышенного лимита'],
                ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'negative'],
            ],
            'is_final' => true,
        ],

    ],
];
