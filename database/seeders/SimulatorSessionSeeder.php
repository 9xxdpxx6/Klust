<?php

namespace Database\Seeders;

use App\Models\Simulator;
use App\Models\SimulatorSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SimulatorSessionSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $simulators = Simulator::all();

        if ($simulators->isEmpty()) {
            return;
        }

        // 200 сессий с разнообразными датами (от 6 месяцев назад до сейчас)
        // Некоторые студенты играли несколько раз
        for ($i = 0; $i < 200; $i++) {
            $student = $students->random();
            $simulator = $simulators->random();
            
            // Дата начала сессии (от 6 месяцев назад до сейчас)
            $startedAt = fake()->dateTimeBetween('-6 months', 'now');
            
            // 75% сессий завершены
            $isCompleted = fake()->boolean(75);
            
            // Если завершена, то completed_at через 5-120 минут после started_at
            $completedAt = null;
            if ($isCompleted) {
                $completedAt = fake()->dateTimeBetween(
                    $startedAt,
                    min(Carbon::parse($startedAt)->addHours(2), Carbon::now())
                );
            }
            
            // Время, потраченное на сессию (в секундах)
            $timeSpent = $isCompleted 
                ? fake()->numberBetween(300, 7200) // 5 минут - 2 часа для завершенных
                : fake()->numberBetween(60, 1800);  // 1 минута - 30 минут для незавершенных
            
            // Очки зависят от завершения
            $score = $isCompleted 
                ? fake()->numberBetween(50, 1000)
                : fake()->numberBetween(0, 49);
            
            // Состояние игры (только для завершенных)
            $state = $isCompleted ? [
                'level' => fake()->numberBetween(1, 10),
                'completed_tasks' => fake()->numberBetween(1, 5),
                'score_multiplier' => fake()->randomFloat(2, 1.0, 2.5),
            ] : null;

            SimulatorSession::create([
                'user_id' => $student->id,
                'simulator_id' => $simulator->id,
                'state' => $state,
                'score' => $score,
                'time_spent' => $timeSpent,
                'is_completed' => $isCompleted,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'created_at' => $startedAt,
                'updated_at' => $completedAt ?? $startedAt,
            ]);
        }
    }
}
