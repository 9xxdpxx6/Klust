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
            'client_message' => 'Добрый день! Мы с женой хотим узнать об ипотечном кредитовании.',
            'user_options' => [
                [
                    'id' => 'greet_warm',
                    'text' => 'Здравствуйте! Конечно, присаживайтесь. Расскажите, какую недвижимость рассматриваете?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Вежливое приветствие и открытый вопрос'],
                    ],
                ],
                [
                    'id' => 'greet_push',
                    'text' => 'Добрый день! У нас отличные условия — давайте сразу посчитаем платёж?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -5, 'category' => 'service_quality', 'reason' => 'Спешка без выяснения потребности'],
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
            'client_message' => 'Подождите, мы только начали выбирать. Хотелось бы сначала понять общие условия.',
            'user_options' => [
                [
                    'id' => 'recover_polite',
                    'text' => 'Конечно, извините. Давайте по порядку — расскажите, что именно вы ищете.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Исправление ситуации'],
                    ],
                ],
                [
                    'id' => 'recover_dry',
                    'text' => 'Хорошо, слушаю вас.',
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
            'client_message' => 'Мы хотим купить двухкомнатную квартиру. Бюджет примерно 5–6 миллионов. Первоначальный взнос — около миллиона.',
            'user_options' => [
                [
                    'id' => 'need_structured',
                    'text' => 'Понял. Чтобы подобрать оптимальную программу, задам несколько вопросов. Какой у вас совокупный семейный доход?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Структурированный подход к выявлению потребности'],
                    ],
                ],
                [
                    'id' => 'need_quick',
                    'text' => '5–6 миллионов, миллион взнос — можем работать. Сколько зарабатываете вместе?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 7, 'category' => 'correctness', 'reason' => 'Быстрый переход к делу'],
                    ],
                ],
                [
                    'id' => 'need_promise',
                    'text' => 'С миллионом взноса — без проблем одобрим. Формальности пройдём быстро.',
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
            'client_message' => 'Вместе выходит около 150 тысяч на руки. Жена — 60, я — 90.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'income', 'value' => 150000],
            ],
            'user_options' => [
                [
                    'id' => 'ask_expenses',
                    'text' => 'Хорошо. Какие у вас сейчас обязательные расходы — коммуналка, аренда, транспорт, дети?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Запрос расходов для оценки платёжеспособности'],
                    ],
                ],
                [
                    'id' => 'skip_to_debts',
                    'text' => '150 тысяч — зафиксировал. Есть действующие кредиты?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 3, 'category' => 'correctness', 'reason' => 'Пропуск вопроса о расходах'],
                        ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 70000],
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
            'client_message' => 'Сейчас снимаем квартиру за 25 тысяч, плюс обязательные расходы — примерно 55 тысяч в общей сложности.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'expenses', 'value' => 55000],
            ],
            'user_options' => [
                [
                    'id' => 'ask_debts',
                    'text' => 'Понял. Есть ли действующие кредиты или рассрочки?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Полная оценка долговой нагрузки'],
                    ],
                ],
                [
                    'id' => 'ask_debts_empathy',
                    'text' => 'Хорошее соотношение. Уточню — есть другие кредитные обязательства?',
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
            'client_message' => 'У жены есть рассрочка на телефон — 3 000 в месяц. Больше ничего.',
            'user_options' => [
                [
                    'id' => 'ask_history',
                    'text' => 'Хорошо, нагрузка минимальная. Были ли просрочки по платежам — у вас или у супруги?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Проверка кредитной дисциплины обоих заёмщиков'],
                    ],
                ],
                [
                    'id' => 'ask_history_direct',
                    'text' => '3 000 — мелочь. Просрочки были?',
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
        | 6. Кредитная история
        |--------------------------------------------------------------------------
        */

        'client_history' => [
            'client_message' => 'Нет, ни у кого не было. Всё всегда платим вовремя.',
            'user_options' => [
                [
                    'id' => 'check_bki',
                    'text' => 'Отлично. Проверю кредитные истории в НБКИ по обоим заёмщикам — это обязательная процедура.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Проверка КИ через БКИ — обязательная процедура'],
                    ],
                ],
                [
                    'id' => 'trust_word',
                    'text' => 'Хорошо, верю. Сколько вам обоим лет?',
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
                ['type' => 'show_message', 'role' => 'system', 'message' => '🔍 Запрос в НБКИ по основному заёмщику...'],
                ['type' => 'show_message', 'role' => 'system', 'message' => '🔍 Запрос в НБКИ по созаёмщику (супруга)...'],
                ['type' => 'show_message', 'role' => 'system', 'message' => '✅ Кредитные истории положительные. Просрочек не выявлено.'],
                ['type' => 'update_client_data', 'field' => 'credit_history', 'value' => 'good'],
            ],
            'client_message' => 'Ну как, всё хорошо?',
            'user_options' => [
                [
                    'id' => 'bki_reassure',
                    'text' => 'Да, обе истории чистые. Уточню возраст — это влияет на максимальный срок ипотеки.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Информирование о результатах проверки'],
                    ],
                ],
                [
                    'id' => 'bki_brief',
                    'text' => 'Чисто. Сколько вам лет?',
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
            'client_message' => 'Мне 34, жене 31.',
            'on_enter_actions' => [
                ['type' => 'update_client_data', 'field' => 'age', 'value' => 34],
            ],
            'user_options' => [
                [
                    'id' => 'run_scoring',
                    'text' => 'Отлично, все данные есть. Рассчитываю параметры ипотечного кредита.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'correctness', 'reason' => 'Корректный переход к расчёту'],
                        ['type' => 'calculate_scoring'],
                        ['type' => 'calculate_credit'],
                    ],
                ],
                [
                    'id' => 'run_scoring_comment',
                    'text' => 'Молодая семья — хорошо, будут льготные программы. Секунду, считаю.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Упоминание льготных программ — полезно, но преждевременно'],
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
            'client_message' => 'Ну что, одобряют нам?',
            'show_calculations' => true,
            'user_options' => [
                [
                    'id' => 'present_balanced',
                    'text' => 'Да, предварительное одобрение есть. Вот параметры — обратите внимание на ежемесячный платёж и общую переплату.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 20, 'category' => 'correctness', 'reason' => 'Сбалансированная презентация с акцентом на переплату'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'medium'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'low'],
                    ],
                ],
                [
                    'id' => 'present_max',
                    'text' => 'Одобрено! Можем дать даже больше — до 7 миллионов. Может, посмотрите квартиру побольше?',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -15, 'category' => 'compliance', 'reason' => 'Навязывание завышенной суммы'],
                        ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'high'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'medium'],
                    ],
                ],
            ],
            'next_stage' => [
                'present_balanced' => 'client_satisfied',
                'present_max' => 'client_concern',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 10. Тревога клиента
        |--------------------------------------------------------------------------
        */

        'client_concern' => [
            'client_message' => '7 миллионов? Нет, мы чётко определились с бюджетом. Не хотим переплачивать больше нужного.',
            'user_options' => [
                [
                    'id' => 'backtrack',
                    'text' => 'Понимаю, вы правы — лучше взять комфортную сумму. Вернёмся к вашему варианту.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'service_quality', 'reason' => 'Корректировка по запросу клиента'],
                    ],
                ],
                [
                    'id' => 'insist',
                    'text' => 'Это просто одобренный лимит, не обязательно его весь брать. Но запас не помешает.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -20, 'category' => 'compliance', 'reason' => 'Настаивание на завышенной сумме'],
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
            'client_message' => 'Ну... ладно, оставьте как есть. Давайте оформлять.',
            'user_options' => [
                [
                    'id' => 'proceed_risky',
                    'text' => 'Отлично! Потребуются паспорта обоих супругов и справки о доходах.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -5, 'category' => 'compliance', 'reason' => 'Оформление при дискомфорте клиента'],
                    ],
                ],
                [
                    'id' => 'reconsider',
                    'text' => 'Знаете, давайте всё-таки посчитаем под ваш комфортный бюджет. Ипотека — это надолго.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'service_quality', 'reason' => 'Ответственное решение в пользу клиента'],
                        ['type' => 'update_client_data', 'field' => 'npl_risk', 'value' => 'low'],
                        ['type' => 'update_client_data', 'field' => 'future_default_flag', 'value' => false],
                    ],
                ],
            ],
            'next_stage' => [
                'proceed_risky' => 'collect_documents_risky',
                'reconsider' => 'client_satisfied',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 11. Клиент доволен условиями
        |--------------------------------------------------------------------------
        */

        'client_satisfied' => [
            'client_message' => 'Условия нас устраивают. Что нужно для оформления?',
            'user_options' => [
                [
                    'id' => 'ask_documents',
                    'text' => 'Для оформления потребуются: паспорта обоих супругов, справки 2-НДФЛ, копии трудовых книжек, и документы на объект недвижимости.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 15, 'category' => 'compliance', 'reason' => 'Полный перечень документов для ипотеки'],
                    ],
                ],
                [
                    'id' => 'skip_docs',
                    'text' => 'Принесите паспорта, остальное решим по ходу.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => -10, 'category' => 'compliance', 'reason' => 'Неполный перечень документов'],
                    ],
                ],
            ],
            'next_stage' => [
                'ask_documents' => 'collect_documents',
                'skip_docs' => 'completion_incomplete',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 12. Сбор документов
        |--------------------------------------------------------------------------
        */

        'collect_documents' => [
            'client_message' => 'Хорошо, всё принесём. Спасибо за подробную консультацию!',
            'user_options' => [
                [
                    'id' => 'finalize_proper',
                    'text' => 'Пожалуйста! Когда соберёте документы — приходите, подготовим договор. Обязательно ознакомьтесь с условиями перед подписанием.',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 10, 'category' => 'compliance', 'reason' => 'Напоминание об ознакомлении с договором'],
                    ],
                ],
                [
                    'id' => 'finalize_quick',
                    'text' => 'Жду вас с документами. До свидания!',
                    'actions' => [
                        ['type' => 'add_score_points', 'points' => 5, 'category' => 'service_quality', 'reason' => 'Завершение без дополнительных рекомендаций'],
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
        | 12а. Документы (опасный путь)
        |--------------------------------------------------------------------------
        */

        'collect_documents_risky' => [
            'client_message' => 'Вот наши паспорта.',
            'user_options' => [
                [
                    'id' => 'finalize_risky',
                    'text' => 'Спасибо, оформляю заявку. Подпишите здесь.',
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
            'client_message' => 'Спасибо большое! Будем собирать документы. Всего доброго!',
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 13а. Неполное завершение
        |--------------------------------------------------------------------------
        */

        'completion_incomplete' => [
            'client_message' => 'Ладно, разберёмся. До свидания.',
            'on_enter_actions' => [
                ['type' => 'add_score_points', 'points' => -5, 'category' => 'compliance', 'reason' => 'Клиент ушёл с неполной информацией о требуемых документах'],
            ],
            'is_final' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | 14. NPL-событие
        |--------------------------------------------------------------------------
        */

        'future_default_event' => [
            'client_message' => '⚠️ Через 14 месяцев: семья не справляется с ипотечными платежами. Просрочка свыше 90 дней. Кредит переведён в категорию NPL.',
            'on_enter_actions' => [
                ['type' => 'add_score_points', 'points' => -30, 'category' => 'correctness', 'reason' => 'Ипотека перешла в NPL из-за завышенной суммы кредита'],
                ['type' => 'update_client_data', 'field' => 'bank_profit', 'value' => 'negative'],
            ],
            'is_final' => true,
        ],

    ],
];
