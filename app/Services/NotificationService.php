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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Notify partner about new application
     */
    public function notifyPartnerAboutApplication(CaseApplication $application): void
    {
        $case = $application->case;
        $partner = $case->partner;

        if (! $partner || ! $partner->user_id) {
            return;
        }

        // Создать уведомление в системе
        AppNotification::create([
            'user_id' => $partner->user_id,
            'type' => 'new_application',
            'title' => 'Новая заявка на кейс',
            'message' => "Команда {$application->leader->name} подала заявку на ваш кейс \"{$case->title}\"",
            'link' => route('partner.cases.show', $case->id),
            'icon' => 'pi-inbox',
            'action_text' => 'Просмотреть',
        ]);

        // Отправить email (без очереди)
        try {
            $partnerUser = User::find($partner->user_id);
            if ($partnerUser && $partnerUser->email) {
                Mail::to($partnerUser->email)->send(new ApplicationSubmittedMail($application));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send application submitted email: '.$e->getMessage());
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
