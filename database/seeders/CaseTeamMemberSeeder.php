<?php

namespace Database\Seeders;

use App\Models\CaseApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class CaseTeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $applications = CaseApplication::all();

        // Для каждой заявки добавляем участников команды
        foreach ($applications as $application) {
            $case = $application->case;
            $requiredSize = $case->required_team_size;

            // Лидер уже есть, добавляем остальных участников (1 до required_team_size - 1)
            $additionalMembers = fake()->numberBetween(1, max(1, $requiredSize - 1));

            // Исключаем лидера из возможных участников
            $availableStudents = $students->where('id', '!=', $application->leader_id);

            if ($availableStudents->count() > 0) {
                $teamMembers = $availableStudents->random(min($additionalMembers, $availableStudents->count()));

                foreach ($teamMembers as $member) {
                    \DB::table('case_team_members')->insert([
                        'application_id' => $application->id,
                        'user_id' => $member->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
