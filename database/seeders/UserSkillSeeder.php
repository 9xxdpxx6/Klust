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

                // Уровень навыка зависит от того, когда он был получен
                // Более ранние навыки могут быть выше уровня
                $monthsSinceEarned = $earnedAt->diffInMonths($now);
                
                // Максимальный возможный уровень зависит от времени с момента получения навыка
                // Но также учитываем, что не все навыки должны быть на высоком уровне
                $maxPossibleLevel = min(100, 20 + ($monthsSinceEarned * 10));
                
                // Генерируем уровень более реалистично:
                // - Новые навыки (0-1 месяц): 1-5 уровень
                // - Средние навыки (1-3 месяца): 3-15 уровень
                // - Старые навыки (3+ месяцев): 10-максимальный уровень
                if ($monthsSinceEarned <= 1) {
                    $level = fake()->numberBetween(1, 5);
                } elseif ($monthsSinceEarned <= 3) {
                    $level = fake()->numberBetween(3, min(15, $maxPossibleLevel));
                } else {
                    // Для старых навыков уровень может быть выше, но не всегда максимальный
                    $level = fake()->numberBetween(5, min($maxPossibleLevel, fake()->numberBetween(20, 50)));
                }
                
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
                
                // Генерируем очки между текущим порогом и следующим (с небольшим разбросом)
                // Очки должны быть в диапазоне от 10% до 90% прогресса к следующему уровню
                $pointsRange = $nextThreshold - $currentThreshold;
                $minPoints = $currentThreshold + (int)($pointsRange * 0.1);
                $maxPoints = $currentThreshold + (int)($pointsRange * 0.9);
                $pointsEarned = fake()->numberBetween($minPoints, $maxPoints);

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
    }
}
