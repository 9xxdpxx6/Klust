<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProgressLog;
use App\Models\SimulatorSession;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProgressLogSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();

        if ($students->isEmpty()) {
            return;
        }

        $sessions = SimulatorSession::where('is_completed', true)
            ->with(['simulator.cases.skills', 'user'])
            ->get();

        $skills = Skill::all();

        if ($skills->isEmpty()) {
            return;
        }

        // Создаем логи для завершенных симуляторов (70% записей)
        $simulatorLogsCount = (int) (500 * 0.7);
        $processedSessions = 0;

        foreach ($sessions as $session) {
            if ($processedSessions >= $simulatorLogsCount) {
                break;
            }

            $simulator = $session->simulator;
            $case = $simulator->cases()->first();

            if (! $case || $case->skills->isEmpty()) {
                continue;
            }

            // Рассчитываем очки на основе score
            $pointsEarned = $this->calculatePointsFromScore($session->score);

            // Для каждого навыка кейса создаем ProgressLog
            foreach ($case->skills as $skill) {
                $createdAt = $session->completed_at ?? $session->started_at ?? Carbon::now();

            // Если дата в будущем, используем текущую дату
            if ($createdAt > Carbon::now()) {
                $createdAt = Carbon::now();
            }

            ProgressLog::create([
                    'user_id' => $session->user_id,
                    'action' => 'simulator_completion',
                    'loggable_type' => Skill::class,
                    'loggable_id' => $skill->id,
                    'points_earned' => $pointsEarned,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

                $processedSessions++;

                if ($processedSessions >= $simulatorLogsCount) {
                    break 2;
                }
            }
        }

        // Создаем логи с ручным начислением очков (30% записей)
        $manualLogsCount = 500 - $processedSessions;

        for ($i = 0; $i < $manualLogsCount; $i++) {
            $student = $students->random();
            $skill = $skills->random();
            $pointsEarned = fake()->numberBetween(10, 100);

            ProgressLog::create([
                'user_id' => $student->id,
                'action' => 'manual',
                'loggable_type' => Skill::class,
                'loggable_id' => $skill->id,
                'points_earned' => $pointsEarned,
                'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            ]);
        }

        // Создаем много логов прогресса для тестового студента
        $testStudent = User::where('email', 'zxc@zxc.zxc')->first();
        if ($testStudent && $skills->isNotEmpty()) {
            // 200 дополнительных логов для тестового студента
            for ($i = 0; $i < 200; $i++) {
                $skill = $skills->random();
                $action = fake()->randomElement(['simulator_completion', 'manual']);
                
                $pointsEarned = match ($action) {
                    'simulator_completion' => fake()->numberBetween(50, 500),
                    'manual' => fake()->numberBetween(10, 100),
                    default => fake()->numberBetween(10, 100),
                };

                ProgressLog::create([
                    'user_id' => $testStudent->id,
                    'action' => $action,
                    'loggable_type' => Skill::class,
                    'loggable_id' => $skill->id,
                    'points_earned' => $pointsEarned,
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }
    }

    /**
     * Calculate points from simulator score
     * Логика из ProgressLogService::calculatePointsFromScore
     */
    private function calculatePointsFromScore(?int $score): int
    {
        if ($score === null) {
            return 10; // Default points
        }

        // Example: 1 point per score point, with bonus for high scores
        if ($score >= 90) {
            return $score + 20; // Bonus for excellent performance
        }

        if ($score >= 75) {
            return $score + 10; // Bonus for good performance
        }

        if ($score >= 50) {
            return $score; // Standard points
        }

        return max(10, (int) ($score * 0.5)); // Minimum 10 points
    }
}
