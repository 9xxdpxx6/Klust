<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\User;
use App\Notifications\ApplicationApprovedNotification;
use App\Notifications\ApplicationRejectedNotification;
use App\Notifications\ApplicationStatusChangedNotification;
use App\Notifications\ApplicationSubmittedNotification;
use App\Notifications\BadgeEarnedNotification;
use App\Notifications\NewCasePublishedNotification;
use App\Notifications\SimulatorCompletedNotification;
use App\Notifications\SkillLevelUpNotification;
use App\Notifications\TeamMemberAddedNotification;
use Illuminate\Support\Facades\Log;

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
                'case:id,title,user_id,required_team_size',
                'case.partnerUser:id,name',
                'teamMembers:id,application_id'
            ]);

            $case = $application->case;
            $partnerUser = $case->partnerUser;

            if (! $partnerUser) {
                return;
            }

            // Отправить уведомление через стандартную систему Laravel
            try {
                $partnerUser->notify(new ApplicationSubmittedNotification($application));
            } catch (\Exception $e) {
                Log::error('Failed to notify partner about application', [
                    'application_id' => $application->id,
                    'partner_id' => $partnerUser->id,
                    'error' => $e->getMessage(),
                ]);
                // Не прерываем выполнение, если уведомление не создалось
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
        // Notify leader
        if ($application->leader) {
            try {
                $application->leader->notify(new ApplicationApprovedNotification($application));
            } catch (\Exception $e) {
                Log::error('Failed to notify leader about approval', [
                    'application_id' => $application->id,
                    'leader_id' => $application->leader_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify team members
        foreach ($application->teamMembers as $teamMember) {
            if ($teamMember->user) {
                try {
                    $teamMember->user->notify(new ApplicationApprovedNotification($application));
                } catch (\Exception $e) {
                    Log::error('Failed to notify team member about approval', [
                        'application_id' => $application->id,
                        'team_member_id' => $teamMember->user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
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
            try {
                $student->notify(new NewCasePublishedNotification($case));
            } catch (\Exception $e) {
                Log::error('Failed to notify student about new case', [
                    'case_id' => $case->id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Notify about application rejection
     */
    public function notifyApplicationRejection(CaseApplication $application, ?string $comment = null): void
    {
        // Notify leader
        if ($application->leader) {
            try {
                $application->leader->notify(new ApplicationRejectedNotification($application));
            } catch (\Exception $e) {
                Log::error('Failed to notify leader about rejection', [
                    'application_id' => $application->id,
                    'leader_id' => $application->leader_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify team members
        foreach ($application->teamMembers as $teamMember) {
            if ($teamMember->user) {
                try {
                    $teamMember->user->notify(new ApplicationRejectedNotification($application));
                } catch (\Exception $e) {
                    Log::error('Failed to notify team member about rejection', [
                        'application_id' => $application->id,
                        'team_member_id' => $teamMember->user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Notify student about badge earned
     */
    public function notifyBadgeEarned(User $user, \App\Models\Badge $badge): void
    {
        try {
            $user->notify(new BadgeEarnedNotification($badge));
        } catch (\Exception $e) {
            Log::error('Failed to notify user about badge earned', [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify student about skill level up
     */
    public function notifySkillLevelUp(User $user, string $skillName, int $newLevel): void
    {
        try {
            $user->notify(new SkillLevelUpNotification($skillName, $newLevel));
        } catch (\Exception $e) {
            Log::error('Failed to notify user about skill level up', [
                'user_id' => $user->id,
                'skill_name' => $skillName,
                'new_level' => $newLevel,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user about being added to a team
     */
    public function notifyUserAboutTeamAddition(User $newMember, CaseApplication $application): void
    {
        try {
            $newMember->notify(new TeamMemberAddedNotification($application));
        } catch (\Exception $e) {
            Log::error('Failed to notify user about team addition', [
                'user_id' => $newMember->id,
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify student about simulator completion
     */
    public function notifySimulatorCompletion(User $student, string $simulatorName, int $pointsEarned): void
    {
        try {
            $student->notify(new SimulatorCompletedNotification($simulatorName, $pointsEarned));
        } catch (\Exception $e) {
            Log::error('Failed to notify student about simulator completion', [
                'user_id' => $student->id,
                'simulator_name' => $simulatorName,
                'points_earned' => $pointsEarned,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify team about application status change
     * Уведомляет всех участников команды (лидера и членов) об изменении статуса заявки
     */
    public function notifyTeamAboutStatusChange(
        CaseApplication $application,
        string $oldStatusName,
        string $newStatusName,
        ?string $comment = null
    ): void {
        // Загружаем необходимые связи
        $application->load([
            'leader:id,name,email',
            'teamMembers.user:id,name,email',
            'case:id,title',
            'status:id,name,label',
        ]);

        // Уведомляем лидера
        if ($application->leader) {
            try {
                $application->leader->notify(
                    new ApplicationStatusChangedNotification(
                        $application,
                        $oldStatusName,
                        $newStatusName,
                        $comment
                    )
                );
            } catch (\Exception $e) {
                Log::error('Failed to notify leader about status change', [
                    'application_id' => $application->id,
                    'leader_id' => $application->leader_id,
                    'old_status' => $oldStatusName,
                    'new_status' => $newStatusName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Уведомляем участников команды
        foreach ($application->teamMembers as $teamMember) {
            if ($teamMember->user) {
                try {
                    $teamMember->user->notify(
                        new ApplicationStatusChangedNotification(
                            $application,
                            $oldStatusName,
                            $newStatusName,
                            $comment
                        )
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to notify team member about status change', [
                        'application_id' => $application->id,
                        'team_member_id' => $teamMember->user_id,
                        'old_status' => $oldStatusName,
                        'new_status' => $newStatusName,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
