<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class UserSkillSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $skills = Skill::all();

        if ($skills->isEmpty()) {
            return;
        }

        // Каждый студент имеет 3-10 навыков с разнообразными датами получения
        foreach ($students as $student) {
            // Дата регистрации студента
            $studentCreatedAt = $student->created_at ?? Carbon::now()->subMonths(6);
            
            // Количество навыков (3-10)
            $studentSkillsCount = fake()->numberBetween(3, 10);
            $randomSkills = $skills->random(min($studentSkillsCount, $skills->count()));

            // Определяем 1-2 топовых навыка для студента (на максимуме или близко к нему)
            $topSkillsCount = fake()->numberBetween(1, min(2, count($randomSkills)));
            $topSkills = $randomSkills->random($topSkillsCount);

            // Навыки получаются постепенно, начиная с даты регистрации
            $skillDates = [];
            $baseDate = Carbon::parse($studentCreatedAt);
            
            foreach ($randomSkills as $index => $skill) {
                // Первые навыки получаются сразу после регистрации
                // Остальные - постепенно в течение следующих месяцев
                $now = Carbon::now();
                
                if ($index === 0) {
                    $earnedAt = $baseDate->copy()->addDays(fake()->numberBetween(1, 7));
                } elseif ($index === 1) {
                    $earnedAt = $baseDate->copy()->addDays(fake()->numberBetween(7, 30));
                } else {
                    // Остальные навыки получаются в течение следующих 1-5 месяцев
                    $startDate = $baseDate->copy()->addDays(30);
                    $endDate = min($baseDate->copy()->addMonths(5), $now);
                    
                    // Убеждаемся, что начальная дата меньше конечной
                    if ($startDate >= $endDate) {
                        $earnedAt = $now->copy()->subDays(fake()->numberBetween(1, 30));
                    } else {
                        // Используем Carbon для генерации даты между двумя датами
                        $daysDiff = $startDate->diffInDays($endDate);
                        $earnedAt = $startDate->copy()->addDays(fake()->numberBetween(0, $daysDiff));
                    }
                }

                // Убеждаемся, что дата не в будущем и не раньше даты регистрации
                if ($earnedAt > $now) {
                    $earnedAt = $now->copy();
                }
                if ($earnedAt < $baseDate) {
                    $earnedAt = $baseDate->copy()->addDays(1);
                }

                // Уровень навыка зависит от max_level навыка и времени получения
                $monthsSinceEarned = $earnedAt->diffInMonths($now);
                $skillMaxLevel = $skill->max_level ?? 100;
                
                // Определяем, является ли этот навык одним из "топовых"
                $isTopSkill = $topSkills->pluck('id')->contains($skill->id);
                
                // Генерируем уровень с учетом max_level навыка
                if ($isTopSkill) {
                    // Топовые навыки: 90-100% от max_level (но не больше max_level)
                    $minLevel = (int)($skillMaxLevel * 0.9);
                    $maxLevel = $skillMaxLevel;
                    $level = fake()->numberBetween($minLevel, $maxLevel);
                } else {
                    // Остальные навыки: 60-95% от max_level, более рассредоточенно
                    // Учитываем время получения навыка
                    $basePercent = 60; // Минимум 60% от max_level
                    
                    // Чем раньше получен навык, тем выше может быть уровень
                    if ($monthsSinceEarned >= 3) {
                        $basePercent = 70; // Старые навыки: 70-95%
                    } elseif ($monthsSinceEarned >= 1) {
                        $basePercent = 65; // Средние навыки: 65-90%
                    }
                    // Новые навыки: 60-80%
                    
                    $minLevel = (int)($skillMaxLevel * ($basePercent / 100));
                    $maxLevel = (int)($skillMaxLevel * 0.95);
                    
                    // Добавляем вариативность: некоторые навыки могут быть ниже
                    if (fake()->boolean(20)) { // 20% шанс быть ниже базового уровня
                        $minLevel = (int)($skillMaxLevel * 0.5);
                        $maxLevel = (int)($skillMaxLevel * 0.7);
                    }
                    
                    $level = fake()->numberBetween($minLevel, $maxLevel);
                }
                
                // Убеждаемся, что уровень не превышает max_level
                $level = min($level, $skillMaxLevel);
                
                // Очки должны соответствовать порогам уровней
                // Используем ту же логику, что и в SkillService
                $levelThresholds = [
                    1 => 0,
                    2 => 50,
                    3 => 120,
                    4 => 210,
                    5 => 320,
                    6 => 450,
                    7 => 600,
                    8 => 770,
                    9 => 960,
                    10 => 1170,
                ];
                
                // Вычисляем порог для текущего уровня
                $currentThreshold = $levelThresholds[$level] ?? 0;
                if ($level > 10) {
                    $levelDiff = $level - 10;
                    if ($level <= 20) {
                        $currentThreshold = 1170 + ($levelDiff * 200) + ($levelDiff * $levelDiff * 10);
                    } elseif ($level <= 50) {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $levelDiff20 = $level - 20;
                        $currentThreshold = $base20 + ($levelDiff20 * 500) + ($levelDiff20 * $levelDiff20 * 5);
                    } else {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $base50 = $base20 + (30 * 500) + (30 * 30 * 5);
                        $levelDiff50 = $level - 50;
                        $currentThreshold = $base50 + ($levelDiff50 * 1000) + ($levelDiff50 * $levelDiff50 * 2);
                    }
                }
                
                // Вычисляем порог для следующего уровня
                $nextLevel = $level + 1;
                $nextThreshold = $levelThresholds[$nextLevel] ?? 0;
                if ($nextLevel > 10) {
                    $levelDiff = $nextLevel - 10;
                    if ($nextLevel <= 20) {
                        $nextThreshold = 1170 + ($levelDiff * 200) + ($levelDiff * $levelDiff * 10);
                    } elseif ($nextLevel <= 50) {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $levelDiff20 = $nextLevel - 20;
                        $nextThreshold = $base20 + ($levelDiff20 * 500) + ($levelDiff20 * $levelDiff20 * 5);
                    } else {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $base50 = $base20 + (30 * 500) + (30 * 30 * 5);
                        $levelDiff50 = $nextLevel - 50;
                        $nextThreshold = $base50 + ($levelDiff50 * 1000) + ($levelDiff50 * $levelDiff50 * 2);
                    }
                }
                
                // Генерируем очки с более рассредоточенным прогрессом
                // Прогресс должен быть равномерно распределен от 5% до 95% к следующему уровню
                $pointsRange = $nextThreshold - $currentThreshold;
                
                // Для более реалистичного распределения используем разные диапазоны
                $progressPercent = fake()->numberBetween(5, 95); // Случайный процент прогресса
                $pointsEarned = $currentThreshold + (int)($pointsRange * ($progressPercent / 100));

                // Для updated_at убеждаемся, что дата корректна
                $updatedAt = $earnedAt;
                if (fake()->boolean(30)) {
                    $updateEndDate = min($earnedAt->copy()->addMonths(1), $now);
                    if ($earnedAt < $updateEndDate) {
                        // Используем Carbon для генерации даты между двумя датами
                        $daysDiff = $earnedAt->diffInDays($updateEndDate);
                        $updatedAt = $earnedAt->copy()->addDays(fake()->numberBetween(0, max(0, $daysDiff)));
                    }
                }

                $student->skills()->attach($skill->id, [
                    'level' => $level,
                    'points_earned' => $pointsEarned,
                    'created_at' => $earnedAt,
                    'updated_at' => $updatedAt,
                ]);
            }
        }

        // Создаем много навыков для тестового студента
        $testStudent = User::where('email', 'zxc@zxc.zxc')->first();
        if ($testStudent && $skills->isNotEmpty()) {
            // Тестовый студент имеет все навыки с высокими уровнями
            $studentCreatedAt = $testStudent->created_at ?? Carbon::now()->subMonths(6);
            $baseDate = Carbon::parse($studentCreatedAt);
            
            foreach ($skills as $index => $skill) {
                // Навыки получаются постепенно
                if ($index === 0) {
                    $earnedAt = $baseDate->copy()->addDays(1);
                } elseif ($index === 1) {
                    $earnedAt = $baseDate->copy()->addDays(7);
                } else {
                    $earnedAt = fake()->dateTimeBetween(
                        $baseDate->copy()->addDays(30),
                        min($baseDate->copy()->addMonths(5), Carbon::now())
                    );
                }

                if ($earnedAt > Carbon::now()) {
                    $earnedAt = Carbon::now();
                }
                if ($earnedAt < $baseDate) {
                    $earnedAt = $baseDate->copy()->addDays(1);
                }

                $monthsSinceEarned = Carbon::parse($earnedAt)->diffInMonths(Carbon::now());
                $skillMaxLevel = $skill->max_level ?? 100;
                
                // Для тестового студента: большинство навыков близко к максимуму
                // 1-2 навыка на максимуме, остальные 85-98% от max_level
                $isTopSkill = ($index < 2) && fake()->boolean(50);
                
                if ($isTopSkill) {
                    // Топовые навыки: 95-100% от max_level
                    $minLevel = (int)($skillMaxLevel * 0.95);
                    $level = fake()->numberBetween($minLevel, $skillMaxLevel);
                } else {
                    // Остальные навыки: 85-98% от max_level
                    $minLevel = (int)($skillMaxLevel * 0.85);
                    $maxLevel = (int)($skillMaxLevel * 0.98);
                    $level = fake()->numberBetween($minLevel, $maxLevel);
                }
                
                $level = min($level, $skillMaxLevel);

                // Вычисляем очки для уровня
                $levelThresholds = [
                    1 => 0, 2 => 50, 3 => 120, 4 => 210, 5 => 320,
                    6 => 450, 7 => 600, 8 => 770, 9 => 960, 10 => 1170,
                ];
                
                $currentThreshold = $levelThresholds[$level] ?? 0;
                if ($level > 10) {
                    $levelDiff = $level - 10;
                    if ($level <= 20) {
                        $currentThreshold = 1170 + ($levelDiff * 200) + ($levelDiff * $levelDiff * 10);
                    } elseif ($level <= 50) {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $levelDiff20 = $level - 20;
                        $currentThreshold = $base20 + ($levelDiff20 * 500) + ($levelDiff20 * $levelDiff20 * 5);
                    } else {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $base50 = $base20 + (30 * 500) + (30 * 30 * 5);
                        $levelDiff50 = $level - 50;
                        $currentThreshold = $base50 + ($levelDiff50 * 1000) + ($levelDiff50 * $levelDiff50 * 2);
                    }
                }
                
                $nextLevel = $level + 1;
                $nextThreshold = $levelThresholds[$nextLevel] ?? 0;
                if ($nextLevel > 10) {
                    $levelDiff = $nextLevel - 10;
                    if ($nextLevel <= 20) {
                        $nextThreshold = 1170 + ($levelDiff * 200) + ($levelDiff * $levelDiff * 10);
                    } elseif ($nextLevel <= 50) {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $levelDiff20 = $nextLevel - 20;
                        $nextThreshold = $base20 + ($levelDiff20 * 500) + ($levelDiff20 * $levelDiff20 * 5);
                    } else {
                        $base20 = 1170 + (10 * 200) + (10 * 10 * 10);
                        $base50 = $base20 + (30 * 500) + (30 * 30 * 5);
                        $levelDiff50 = $nextLevel - 50;
                        $nextThreshold = $base50 + ($levelDiff50 * 1000) + ($levelDiff50 * $levelDiff50 * 2);
                    }
                }
                
                $pointsRange = $nextThreshold - $currentThreshold;
                
                // Для тестового студента прогресс более рассредоточен
                $progressPercent = fake()->numberBetween(10, 90);
                $pointsEarned = $currentThreshold + (int)($pointsRange * ($progressPercent / 100));

                $testStudent->skills()->syncWithoutDetaching([
                    $skill->id => [
                        'level' => $level,
                        'points_earned' => $pointsEarned,
                        'created_at' => $earnedAt,
                        'updated_at' => $earnedAt,
                    ]
                ]);
            }
        }
    }
}
