<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\CaseApplication;
use App\Models\ProgressLog;
use App\Models\SimulatorSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProgressLogSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $sessions = SimulatorSession::where('is_completed', true)->get();
        $applications = CaseApplication::all();
        $badges = Badge::all();

        if ($sessions->isEmpty() || $applications->isEmpty() || $badges->isEmpty()) {
            return;
        }

        // 500 логов прогресса с разнообразными датами
        $actionWeights = [
            'completed_simulator' => 40,  // 40% - завершение симуляторов
            'applied_to_case' => 30,      // 30% - подача заявок
            'earned_badge' => 20,          // 20% - получение бейджей
            'joined_team' => 10,          // 10% - присоединение к команде
        ];

        for ($i = 0; $i < 500; $i++) {
            $action = fake()->randomElement(array_merge(
                array_fill(0, $actionWeights['completed_simulator'], 'completed_simulator'),
                array_fill(0, $actionWeights['applied_to_case'], 'applied_to_case'),
                array_fill(0, $actionWeights['earned_badge'], 'earned_badge'),
                array_fill(0, $actionWeights['joined_team'], 'joined_team')
            ));

            $loggable = match ($action) {
                'completed_simulator' => $sessions->random(),
                'applied_to_case', 'joined_team' => $applications->random(),
                'earned_badge' => $badges->random(),
                default => null,
            };

            if (! $loggable) {
                continue;
            }

            // Дата лога зависит от типа действия
            $createdAt = match ($action) {
                'completed_simulator' => $loggable->completed_at ?? $loggable->started_at,
                'applied_to_case' => $loggable->submitted_at,
                'joined_team' => fake()->dateTimeBetween($loggable->submitted_at, 'now'),
                'earned_badge' => fake()->dateTimeBetween('-6 months', 'now'),
                default => fake()->dateTimeBetween('-6 months', 'now'),
            };

            // Если дата в будущем, используем текущую дату
            if ($createdAt > Carbon::now()) {
                $createdAt = Carbon::now();
            }

            ProgressLog::create([
                'user_id' => $students->random()->id,
                'action' => $action,
                'loggable_type' => get_class($loggable),
                'loggable_id' => $loggable->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
