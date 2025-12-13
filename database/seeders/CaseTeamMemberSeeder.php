<?php

namespace Database\Seeders;

use App\Models\CaseApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CaseTeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $applications = CaseApplication::all();

        // Для каждой заявки добавляем участников команды
        foreach ($applications as $application) {
            $case = $application->case;
            if (!$case) {
                continue;
            }
            
            $requiredSize = $case->required_team_size;

            // Лидер уже есть, добавляем остальных участников (1 до required_team_size - 1)
            $additionalMembers = fake()->numberBetween(1, max(1, $requiredSize - 1));

            // Исключаем лидера из возможных участников
            $availableStudents = $students->where('id', '!=', $application->leader_id);

            if ($availableStudents->count() > 0) {
                $teamMembers = $availableStudents->random(min($additionalMembers, $availableStudents->count()));

                // Дата подачи заявки
                $submittedAt = $application->submitted_at ?? $application->created_at;
                
                // Участники присоединяются в разное время после подачи заявки
                // Но до рассмотрения (если заявка уже рассмотрена)
                $maxDate = $application->reviewed_at 
                    ? min($application->reviewed_at, Carbon::now())
                    : Carbon::now();

                foreach ($teamMembers as $index => $member) {
                    // Первый участник присоединяется сразу после подачи заявки
                    // Остальные - в течение следующих 1-7 дней
                    if ($index === 0) {
                        $joinedAt = Carbon::parse($submittedAt)->addHours(fake()->numberBetween(1, 24));
                    } else {
                        $joinedAt = fake()->dateTimeBetween(
                            $submittedAt,
                            min(Carbon::parse($submittedAt)->addDays(7), $maxDate)
                        );
                    }

                    // Убеждаемся, что дата не в будущем
                    if ($joinedAt > Carbon::now()) {
                        $joinedAt = Carbon::now();
                    }

                    \DB::table('case_team_members')->insert([
                        'application_id' => $application->id,
                        'user_id' => $member->id,
                        'created_at' => $joinedAt,
                        'updated_at' => $joinedAt,
                    ]);
                }
            }
        }
    }
}
