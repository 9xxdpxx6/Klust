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
            'model_path' => '/models/characters/female1.glb',
            'age_range' => [18, 25],
            'income_range' => [20000, 50000],
            'expenses_range' => [15000, 40000],
            'credit_history_options' => ['none', 'fair', 'good'],
            'has_deposit_probability' => 0.2,
        ],
        'entrepreneur' => [
            'type' => 'entrepreneur',
            'model_path' => '/models/characters/male1.glb',
            'age_range' => [30, 50],
            'income_range' => [100000, 500000],
            'expenses_range' => [60000, 300000],
            'credit_history_options' => ['good', 'excellent'],
            'has_deposit_probability' => 0.6,
        ],
    ],
];
