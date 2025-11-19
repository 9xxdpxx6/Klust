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

        // Каждый студент имеет 1-5 бейджей
        foreach ($students as $student) {
            $badgesCount = fake()->numberBetween(1, 5);
            $randomBadges = $badges->random($badgesCount);

            foreach ($randomBadges as $badge) {
                $student->badges()->attach($badge->id, [
                    'earned_at' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }
    }
}
