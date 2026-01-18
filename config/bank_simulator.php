<?php

return [
    'scoring' => [
        'weights' => [
            'income' => 0.3,
            'expenses' => 0.25,
            'age' => 0.2,
            'credit_history' => 0.25,
        ],
        'thresholds' => [
            'auto_approve' => 0.8,
            'approve_with_conditions' => 0.5,
            'manual_review' => 0.3,
            'auto_reject' => 0.0,
        ],
    ],
    'client_templates' => [
        'student' => [
            'type' => 'student',
            'name' => 'Иванов А.П.',
            'age' => 25,
            'income' => 80000,
            'expenses' => 60000,
            'credit_history' => 'good',
            'has_deposit' => false,
        ],
    ],
];
