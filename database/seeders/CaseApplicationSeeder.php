<?php

namespace Database\Seeders;

use App\Models\ApplicationStatus;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CaseApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $cases = CaseModel::whereIn('status', ['active', 'completed'])->get();

        if ($cases->isEmpty()) {
            return;
        }

        $pendingId = ApplicationStatus::getIdByName('pending');
        $acceptedId = ApplicationStatus::getIdByName('accepted');
        $rejectedId = ApplicationStatus::getIdByName('rejected');

        // 120 заявок на кейсы с разнообразными датами и статусами
        for ($i = 0; $i < 120; $i++) {
            $case = $cases->random();
            $leader = $students->random();
            
            // Дата подачи заявки (от 5 месяцев назад до сейчас)
            $submittedAt = fake()->dateTimeBetween('-5 months', 'now');
            
            // Определяем статус с весами
            $statusWeights = [
                'pending' => 30,   // 30% ожидают рассмотрения
                'accepted' => 45,   // 45% приняты
                'rejected' => 25,   // 25% отклонены
            ];
            
            $statusId = fake()->randomElement(array_merge(
                array_fill(0, $statusWeights['pending'], $pendingId),
                array_fill(0, $statusWeights['accepted'], $acceptedId),
                array_fill(0, $statusWeights['rejected'], $rejectedId)
            ));

            // Если заявка не pending, то есть дата рассмотрения
            $reviewedAt = null;
            $partnerComment = null;
            $rejectionReason = null;
            
            if ($statusId !== $pendingId) {
                // Рассмотрена через 1-14 дней после подачи
                $reviewedAt = fake()->dateTimeBetween($submittedAt, min(Carbon::parse($submittedAt)->addDays(14), Carbon::now()));
                
                if ($statusId === $acceptedId) {
                    // Для принятых заявок - положительный комментарий
                    $partnerComment = fake()->randomElement([
                        'Отличная мотивация и команда!',
                        'Хорошо проработанная заявка.',
                        'Команда имеет необходимые навыки.',
                        'Интересный подход к решению задачи.',
                        'Рекомендую к принятию.',
                    ]);
                } else {
                    // Для отклоненных - причина отклонения
                    $rejectionReason = fake()->randomElement([
                        'Недостаточно опыта у команды.',
                        'Заявка не соответствует требованиям.',
                        'Команда не подходит по составу.',
                        'Уже выбрана другая команда.',
                        'Кейс закрыт.',
                    ]);
                }
            }

            $createdAt = $submittedAt;
            $updatedAt = $reviewedAt ?? $submittedAt;

            CaseApplication::create([
                'case_id' => $case->id,
                'leader_id' => $leader->id,
                'motivation' => fake()->paragraphs(2, true),
                'status_id' => $statusId,
                'rejection_reason' => $rejectionReason,
                'partner_comment' => $partnerComment,
                'reviewed_at' => $reviewedAt,
                'submitted_at' => $submittedAt,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }
}
