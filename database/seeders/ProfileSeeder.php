<?php

namespace Database\Seeders;

use App\Models\PartnerProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Студенческие профили (100)
        $students = User::role('student')->get();
        foreach ($students as $student) {
            StudentProfile::factory()->create([
                'user_id' => $student->id,
            ]);
        }

        // Профили партнёров (10)
        $partners = User::role('partner')->get();
        foreach ($partners as $partner) {
            PartnerProfile::factory()->create([
                'user_id' => $partner->id,
            ]);
        }

        // Профили преподавателей (5)
        $teachers = User::role('teacher')->get();
        foreach ($teachers as $teacher) {
            TeacherProfile::factory()->create([
                'user_id' => $teacher->id,
            ]);
        }
    }
}
