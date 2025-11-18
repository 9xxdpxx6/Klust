<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\CaseApplication;
use App\Models\ProgressLog;
use App\Models\SimulatorSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgressLogSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $sessions = SimulatorSession::all();
        $applications = CaseApplication::all();
        $badges = Badge::all();

        // ~300 логов прогресса
        for ($i = 0; $i < 300; $i++) {
            $action = fake()->randomElement([
                'earned_badge',
                'applied_to_case',
                'completed_simulator',
                'joined_team',
            ]);

            $loggable = match ($action) {
                'completed_simulator' => $sessions->random(),
                'applied_to_case', 'joined_team' => $applications->random(),
                'earned_badge' => $badges->random(),
                default => null,
            };

            if (! $loggable) {
                continue; // Пропускаем если нет loggable
            }

            ProgressLog::factory()->create([
                'user_id' => $students->random()->id,
                'action' => $action,
                'loggable_type' => get_class($loggable),
                'loggable_id' => $loggable->id,
            ]);
        }
    }
}
