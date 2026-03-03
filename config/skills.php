<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Skill Level Thresholds
    |--------------------------------------------------------------------------
    |
    | Defines the minimum points required to reach each level.
    | Used by ProgressLogService, SkillService, and StudentService.
    | Single source of truth — do NOT duplicate these values elsewhere.
    |
    */
    'level_thresholds' => [
        1 => 0,
        2 => 100,
        3 => 250,
        4 => 500,
        5 => 1000,
        6 => 2000,
        7 => 4000,
        8 => 8000,
        9 => 16000,
        10 => 32000,
    ],
];
