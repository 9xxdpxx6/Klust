<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppNotification;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\User;

class NotificationService
{
    /**
     * Notify partner about new application
     */
    public function notifyPartnerAboutApplication(CaseApplication $application): void
    {
        $case = $application->case;
        $partner = $case->partner;

        if (! $partner) {
            return;
        }

        // Get partner user
        $partnerUser = $partner->partnerProfile?->user;

        if (! $partnerUser) {
            return;
        }

        AppNotification::create([
            'user_id' => $partnerUser->id,
            'type' => 'new_application',
            'title' => 'New Application for Case',
            'message' => "New team application from {$application->leader->name} for case \"{$case->title}\"",
            'data' => json_encode([
                'application_id' => $application->id,
                'case_id' => $case->id,
                'leader_name' => $application->leader->name,
            ]),
        ]);
    }

    /**
     * Notify team about application approval
     */
    public function notifyTeamAboutApproval(CaseApplication $application): void
    {
        $case = $application->case;

        // Notify leader
        AppNotification::create([
            'user_id' => $application->leader_id,
            'type' => 'application_approved',
            'title' => 'Application Approved',
            'message' => "Your team application for case \"{$case->title}\" has been approved!",
            'data' => json_encode([
                'application_id' => $application->id,
                'case_id' => $case->id,
                'case_title' => $case->title,
            ]),
        ]);

        // Notify team members
        foreach ($application->teamMembers as $teamMember) {
            AppNotification::create([
                'user_id' => $teamMember->user_id,
                'type' => 'application_approved',
                'title' => 'Team Application Approved',
                'message' => "Your team application for case \"{$case->title}\" has been approved!",
                'data' => json_encode([
                    'application_id' => $application->id,
                    'case_id' => $case->id,
                    'case_title' => $case->title,
                ]),
            ]);
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
            // If no specific skills required, notify all students
            $students = User::role('student')->get();
        } else {
            // Notify students with matching skills
            $students = User::role('student')
                ->whereHas('skills', function ($q) use ($requiredSkillIds) {
                    $q->whereIn('skills.id', $requiredSkillIds);
                })
                ->get();
        }

        foreach ($students as $student) {
            AppNotification::create([
                'user_id' => $student->id,
                'type' => 'new_case',
                'title' => 'New Case Available',
                'message' => "New case available: \"{$case->title}\" from {$case->partner->company_name}",
                'data' => json_encode([
                    'case_id' => $case->id,
                    'case_title' => $case->title,
                    'partner_name' => $case->partner->company_name,
                ]),
            ]);
        }
    }

    /**
     * Notify about application rejection
     */
    public function notifyApplicationRejection(CaseApplication $application): void
    {
        $case = $application->case;

        // Notify leader
        AppNotification::create([
            'user_id' => $application->leader_id,
            'type' => 'application_rejected',
            'title' => 'Application Rejected',
            'message' => "Your team application for case \"{$case->title}\" has been rejected.",
            'data' => json_encode([
                'application_id' => $application->id,
                'case_id' => $case->id,
                'case_title' => $case->title,
                'rejection_reason' => $application->rejection_reason,
            ]),
        ]);

        // Optionally notify team members
        foreach ($application->teamMembers as $teamMember) {
            AppNotification::create([
                'user_id' => $teamMember->user_id,
                'type' => 'application_rejected',
                'title' => 'Team Application Rejected',
                'message' => "Your team application for case \"{$case->title}\" has been rejected.",
                'data' => json_encode([
                    'application_id' => $application->id,
                    'case_id' => $case->id,
                    'case_title' => $case->title,
                ]),
            ]);
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
            'title' => 'New Badge Earned!',
            'message' => "Congratulations! You earned the \"{$badgeName}\" badge!",
            'data' => json_encode([
                'badge_name' => $badgeName,
            ]),
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
            'title' => 'Skill Level Up!',
            'message' => "Your {$skillName} skill has reached level {$newLevel}!",
            'data' => json_encode([
                'skill_name' => $skillName,
                'new_level' => $newLevel,
            ]),
        ]);
    }
}
