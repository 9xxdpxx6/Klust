<?php

declare(strict_types=1);

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

    /*
    |--------------------------------------------------------------------------
    | Evaluation category weights (used by EvaluationService)
    |--------------------------------------------------------------------------
    | Must sum to 1.0
    */
    'evaluation_weights' => [
        'correctness' => 0.40,
        'service_quality' => 0.30,
        'compliance' => 0.30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dialogue type → client type mapping
    |--------------------------------------------------------------------------
    | Ensures each simulator variant gets a thematically appropriate client
    | with a distinct 3D model (where possible).
    */
    'dialogue_client_mapping' => [
        'credit_card'   => 'student',       // Студент оформляет первую карту
        'consumer_loan' => 'entrepreneur',  // Предприниматель берёт кредит
        'mortgage'      => 'family',        // Семья берёт ипотеку
        'deposit'       => 'pensioner',     // Пенсионер открывает вклад
    ],

    /*
    |--------------------------------------------------------------------------
    | Client templates for generation
    |--------------------------------------------------------------------------
    | Each template has a unique 3D model_path where possible.
    */
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
        'family' => [
            'type' => 'family',
            'model_path' => '/models/characters/male2.glb',
            'age_range' => [30, 45],
            'income_range' => [100000, 200000],
            'expenses_range' => [70000, 150000],
            'credit_history_options' => ['good', 'excellent'],
            'has_deposit_probability' => 0.5,
        ],
        'pensioner' => [
            'type' => 'pensioner',
            'model_path' => '/models/characters/female1.glb',
            'age_range' => [55, 70],
            'income_range' => [25000, 50000],
            'expenses_range' => [20000, 40000],
            'credit_history_options' => ['excellent', 'good'],
            'has_deposit_probability' => 0.7,
        ],
    ],
];
