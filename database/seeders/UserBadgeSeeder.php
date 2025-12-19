<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $badges = Badge::all();

        // Каждый студент имеет 1-5 достижений
        foreach ($students as $student) {
            $badgesCount = fake()->numberBetween(1, 5);
            $randomBadges = $badges->random($badgesCount);

            foreach ($randomBadges as $badge) {
                // 30% бейджей имеют уровень больше 1
                $level = fake()->boolean(30) 
                    ? fake()->numberBetween(2, min(5, $badge->max_level ?? 5)) 
                    : 1;

                $student->badges()->attach($badge->id, [
                    'level' => $level,
                    'earned_at' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }

        // Создаем много достижений для тестового студента
        $testStudent = User::where('email', 'zxc@zxc.zxc')->first();
        if ($testStudent && $badges->isNotEmpty()) {
            // Тестовый студент имеет все достижения
            foreach ($badges as $badge) {
                // Для тестового студента 50% бейджей имеют уровень больше 1
                $level = fake()->boolean(50) 
                    ? fake()->numberBetween(2, min(5, $badge->max_level ?? 5)) 
                    : 1;

                $testStudent->badges()->syncWithoutDetaching([
                    $badge->id => [
                        'level' => $level,
                        'earned_at' => fake()->dateTimeBetween('-6 months', 'now'),
                    ]
                ]);
            }
        }
    }
}
