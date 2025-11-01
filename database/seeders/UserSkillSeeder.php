<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSkillSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $skills = Skill::all();

        // Каждый студент имеет 3-8 навыков
        foreach ($students as $student) {
            $studentSkillsCount = fake()->numberBetween(3, 8);
            $randomSkills = $skills->random($studentSkillsCount);

            foreach ($randomSkills as $skill) {
                $level = fake()->numberBetween(0, 100);
                $pointsEarned = (int)($level * 10 * fake()->randomFloat(2, 0.5, 1.5));

                $student->skills()->attach($skill->id, [
                    'level' => $level,
                    'points_earned' => $pointsEarned,
                ]);
            }
        }
    }
}

