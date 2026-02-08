<?php

declare(strict_types=1);

return [
    'stages' => [
        'greeting' => [
            'client_message' => 'Здравствуйте! Чем могу помочь?',
            'user_options' => [
                [
                    'id' => 'credit_card',
                    'text' => 'Мне нужна кредитная карта',
                ],
                [
                    'id' => 'deposit',
                    'text' => 'Хочу открыть вклад',
                ],
                [
                    'id' => 'consultation',
                    'text' => 'Нужна консультация',
                ],
            ],
            'next_stage' => [
                'credit_card' => 'credit_inquiry',
                'deposit' => 'deposit_inquiry',
                'consultation' => 'completion',
            ],
        ],
        'credit_inquiry' => [
            'client_message' => 'Отлично! Расскажите, какую сумму вы хотели бы получить?',
            'required_data' => ['credit_amount'],
            'next_stage' => 'collect_income',
        ],
        'deposit_inquiry' => [
            'client_message' => 'Какую сумму вы хотите внести на вклад?',
            'required_data' => ['deposit_amount', 'deposit_period'],
            'next_stage' => 'collect_income',
        ],
        'collect_income' => [
            'client_message' => 'Расскажите о вашем доходе',
            'required_data' => ['income'],
            'next_stage' => 'collect_expenses',
        ],
        'collect_expenses' => [
            'client_message' => 'Каковы ваши ежемесячные расходы?',
            'required_data' => ['expenses'],
            'next_stage' => 'collect_age_and_history',
        ],
        'collect_age_and_history' => [
            'client_message' => 'Сколько вам лет и какая у вас кредитная история?',
            'required_data' => ['age', 'credit_history'],
            'next_stage' => 'present_results',
        ],
        'present_results' => [
            'client_message' => 'На основе ваших данных, мы можем предложить...',
            'user_options' => [
                [
                    'id' => 'accept',
                    'text' => 'Принять предложение',
                ],
                [
                    'id' => 'reject',
                    'text' => 'Отклонить',
                ],
            ],
            'next_stage' => [
                'accept' => 'completion',
                'reject' => 'completion',
            ],
            'show_calculations' => true,
        ],
        'completion' => [
            'client_message' => 'Спасибо за визит! Обращайтесь еще!',
            'is_final' => true,
        ],
    ],
];
