<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationRejectedMail;
use App\Mail\ApplicationSubmittedMail;
use App\Mail\TeamMemberAddedMail;
use App\Models\AppNotification;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notify partner about new application
     */
    public function notifyPartnerAboutApplication(CaseApplication $application): void
    {
        try {
            // Загружаем все необходимые связи одним запросом для избежания N+1
            $application->load([
                'leader:id,name,email',
                'case:id,title,partner_id,required_team_size',
                'case.partner:id,user_id',
                'teamMembers:id,application_id'
            ]);

            $case = $application->case;
            $partner = $case->partner;

            if (! $partner || ! $partner->user_id) {
                return;
            }

            // Создать уведомление в системе
            try {
                // Генерируем URL заранее, чтобы не было проблем с route()
                $caseUrl = url("/partner/cases/{$case->id}");
                
                AppNotification::create([
                    'user_id' => $partner->user_id,
                    'type' => 'new_application',
                    'title' => 'Новая заявка на кейс',
                    'message' => "Команда {$application->leader->name} подала заявку на ваш кейс \"{$case->title}\"",
                    'link' => $caseUrl,
                    'icon' => 'pi-inbox',
                    'action_text' => 'Просмотреть',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create notification', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
                // Не прерываем выполнение, если уведомление не создалось
            }

            // Отправить email в фоне (не блокирует ответ)
            // В development окружении не отправляем email
            try {
                $partnerUser = User::find($partner->user_id);
                if ($partnerUser && $partnerUser->email) {
                    $applicationId = $application->id;
                    $partnerEmail = $partnerUser->email;
                    
                    // В development/local окружении не отправляем email через SMTP
                    if (!app()->environment(['local', 'testing'])) {
                        // В production отправляем email через очередь
                        dispatch(function () use ($applicationId, $partnerEmail) {
                            try {
                                // Устанавливаем таймаут для выполнения
                                set_time_limit(15);
                                
                                // Загружаем application с нужными связями
                                $app = CaseApplication::with([
                                    'leader:id,name,email',
                                    'case:id,title,required_team_size',
                                    'teamMembers:id,application_id'
                                ])->find($applicationId);
                                
                                if ($app) {
                                    try {
                                        Mail::to($partnerEmail)->send(new ApplicationSubmittedMail($app));
                                    } catch (\Exception $mailException) {
                                        Log::error('Failed to send email', [
                                            'application_id' => $applicationId,
                                            'partner_email' => $partnerEmail,
                                            'error' => $mailException->getMessage(),
                                        ]);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('Failed to send email in job', [
                                    'application_id' => $applicationId,
                                    'partner_email' => $partnerEmail,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        })->afterResponse();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to setup email sending', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify partner', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
            // Не пробрасываем исключение, чтобы не прервать создание заявки
        }
    }

    /**
     * Notify team about application approval
     */
    public function notifyTeamAboutApproval(CaseApplication $application): void
    {
        $case = $application->case;
        $partnerName = $case->partner->company_name;

        // Notify leader
        AppNotification::create([
            'user_id' => $application->leader_id,
            'type' => 'application_approved',
            'title' => 'Заявка одобрена!',
            'message' => "Ваша заявка на кейс \"{$case->title}\" от компании {$partnerName} была одобрена",
            'link' => route('student.team.show', $application->id),
            'icon' => 'pi-check-circle',
            'action_text' => 'Перейти к команде',
        ]);

        // Send email to leader
        try {
            if ($application->leader && $application->leader->email) {
                Mail::to($application->leader->email)->send(new ApplicationApprovedMail($application));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send application approved email to leader: '.$e->getMessage());
        }

        // Notify team members
        foreach ($application->teamMembers as $teamMember) {
            AppNotification::create([
                'user_id' => $teamMember->user_id,
                'type' => 'application_approved',
                'title' => 'Заявка команды одобрена!',
                'message' => "Заявка вашей команды на кейс \"{$case->title}\" была одобрена",
                'link' => route('student.team.show', $application->id),
                'icon' => 'pi-check-circle',
                'action_text' => 'Перейти к команде',
            ]);

            // Send email to team member
            try {
                if ($teamMember->user && $teamMember->user->email) {
                    Mail::to($teamMember->user->email)->send(new ApplicationApprovedMail($application));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send application approved email to team member: '.$e->getMessage());
            }
        }
    }

    /**
     * Notify students about new case
     */
    public function notifyStudentsAboutNewCase(CaseModel $case): void
    {
        // Get students who have skills matching the case requirements
        $requiredSkillIds = $case->skills->pluck('id')->toArray();

        if (empty($requiredSkillIds)) {
            // If no specific skills required, notify all students (limit to prevent spam)
            $students = User::role('student')->limit(100)->get();
        } else {
            // Notify students with matching skills
            $students = User::role('student')
                ->whereHas('skills', function ($q) use ($requiredSkillIds) {
                    $q->whereIn('skills.id', $requiredSkillIds);
                })
                ->limit(100)
                ->get();
        }

        foreach ($students as $student) {
            AppNotification::create([
                'user_id' => $student->id,
                'type' => 'new_case',
                'title' => 'Доступен новый кейс',
                'message' => "Новый кейс от {$case->partner->company_name}: \"{$case->title}\"",
                'link' => route('student.cases.show', $case->id),
                'icon' => 'pi-briefcase',
                'action_text' => 'Просмотреть',
            ]);
        }
    }

    /**
     * Notify about application rejection
     */
    public function notifyApplicationRejection(CaseApplication $application, ?string $comment = null): void
    {
        $case = $application->case;
        $partnerName = $case->partner->company_name;

        $message = "Ваша заявка на кейс \"{$case->title}\" от компании {$partnerName} была отклонена";
        if ($comment) {
            $message .= ". Комментарий: {$comment}";
        }

        // Notify leader
        AppNotification::create([
            'user_id' => $application->leader_id,
            'type' => 'application_rejected',
            'title' => 'Заявка отклонена',
            'message' => $message,
            'link' => route('student.cases.show', $case->id),
            'icon' => 'pi-times-circle',
            'action_text' => 'Просмотреть кейс',
        ]);

        // Send email to leader
        try {
            if ($application->leader && $application->leader->email) {
                Mail::to($application->leader->email)->send(new ApplicationRejectedMail($application, $comment));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send application rejected email to leader: '.$e->getMessage());
        }

        // Notify team members
        foreach ($application->teamMembers as $teamMember) {
            AppNotification::create([
                'user_id' => $teamMember->user_id,
                'type' => 'application_rejected',
                'title' => 'Заявка команды отклонена',
                'message' => "Заявка вашей команды на кейс \"{$case->title}\" была отклонена",
                'link' => route('student.cases.show', $case->id),
                'icon' => 'pi-times-circle',
                'action_text' => 'Просмотреть кейс',
            ]);

            // Send email to team member
            try {
                if ($teamMember->user && $teamMember->user->email) {
                    Mail::to($teamMember->user->email)->send(new ApplicationRejectedMail($application, $comment));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send application rejected email to team member: '.$e->getMessage());
            }
        }
    }

    /**
     * Notify student about badge earned
     */
    public function notifyBadgeEarned(User $user, string $badgeName): void
    {
        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'badge_earned',
            'title' => 'Получен новый бейдж!',
            'message' => "Поздравляем! Вы получили бейдж \"{$badgeName}\"",
            'link' => route('student.badges.index'),
            'icon' => 'pi-star',
            'action_text' => 'Посмотреть бейджи',
        ]);
    }

    /**
     * Notify student about skill level up
     */
    public function notifySkillLevelUp(User $user, string $skillName, int $newLevel): void
    {
        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'skill_level_up',
            'title' => 'Повышение уровня навыка!',
            'message' => "Ваш навык {$skillName} достиг уровня {$newLevel}!",
            'link' => route('student.skills.index'),
            'icon' => 'pi-chart-line',
            'action_text' => 'Посмотреть навыки',
        ]);
    }

    /**
     * Notify user about being added to a team
     */
    public function notifyUserAboutTeamAddition(User $newMember, CaseApplication $application): void
    {
        $leaderName = $application->leader->name;
        $caseName = $application->case->title;

        // Create notification
        AppNotification::create([
            'user_id' => $newMember->id,
            'type' => 'team_addition',
            'title' => 'Вы добавлены в команду',
            'message' => "{$leaderName} добавил(а) вас в команду для кейса \"{$caseName}\"",
            'link' => route('student.team.show', $application->id),
            'icon' => 'pi-users',
            'action_text' => 'Перейти к команде',
        ]);

        // Send email
        try {
            if ($newMember->email) {
                Mail::to($newMember->email)->send(new TeamMemberAddedMail($newMember, $application));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send team member added email: '.$e->getMessage());
        }
    }

    /**
     * Notify student about simulator completion
     */
    public function notifySimulatorCompletion(User $student, string $simulatorName, int $pointsEarned): void
    {
        AppNotification::create([
            'user_id' => $student->id,
            'type' => 'simulator_completed',
            'title' => 'Симулятор пройден!',
            'message' => "Вы успешно завершили симулятор \"{$simulatorName}\" и получили {$pointsEarned} очков",
            'link' => route('student.simulators.index'),
            'icon' => 'pi-trophy',
            'action_text' => 'Просмотреть',
        ]);
    }
}
