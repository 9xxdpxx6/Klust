<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\FilterHelper;
use App\Models\CaseApplication;
use App\Models\Partner;
use App\Models\ProgressLog;
use App\Models\SimulatorSession;
use Illuminate\Support\Collection;

class TeamService
{
    /**
     * Get team progress for application
     */
    public function getTeamProgress(CaseApplication $application): array
    {
        // Load all team members
        $application->load(['leader', 'teamMembers.user', 'case']);

        $allMembers = collect([$application->leader])
            ->merge($application->teamMembers->pluck('user'))
            ->unique('id');

        // Calculate progress metrics
        $totalSkillPoints = $allMembers->sum(function ($member) {
            return $member->studentProfile?->total_points ?? 0;
        });

        $averageSkillPoints = $allMembers->count() > 0
            ? round($totalSkillPoints / $allMembers->count(), 2)
            : 0;

        // Get matching skills with case requirements
        $caseSkillIds = $application->case->skills->pluck('id')->toArray();
        $teamSkillsCount = $allMembers->reduce(function ($carry, $member) use ($caseSkillIds) {
            $memberSkills = $member->skills()->whereIn('skills.id', $caseSkillIds)->count();

            return $carry + $memberSkills;
        }, 0);

        return [
            'team_size' => $allMembers->count(),
            'total_skill_points' => $totalSkillPoints,
            'average_skill_points' => $averageSkillPoints,
            'matching_skills_count' => $teamSkillsCount,
            'case_required_skills' => count($caseSkillIds),
            'members' => $allMembers->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'avatar' => $member->avatar,
                    'total_points' => $member->studentProfile?->total_points ?? 0,
                    'skills_count' => $member->skills()->count(),
                    'badges_count' => $member->badges()->count(),
                ];
            }),
        ];
    }

    /**
     * Get team activity history
     */
    public function getTeamActivityHistory(CaseApplication $application): Collection
    {
        // Get all team member IDs
        $teamMemberIds = $application->teamMembers->pluck('user_id')->push($application->leader_id);

        // Get progress logs for team members related to case skills
        $caseSkillIds = $application->case->skills->pluck('id')->toArray();

        $progressLogs = ProgressLog::whereIn('user_id', $teamMemberIds)
            ->whereIn('skill_id', $caseSkillIds)
            ->with(['user', 'skill'])
            ->get()
            ->map(function ($log) {
                return [
                    'type' => 'skill_progress',
                    'user' => $log->user,
                    'description' => "Earned {$log->points_earned} points in {$log->skill->name}",
                    'date' => $log->created_at,
                    'data' => [
                        'skill' => $log->skill->name,
                        'points' => $log->points_earned,
                    ],
                ];
            });

        // Get simulator sessions completed by team members
        $simulatorSessions = SimulatorSession::whereIn('user_id', $teamMemberIds)
            ->whereNotNull('completed_at')
            ->with(['user', 'simulator'])
            ->get()
            ->map(function ($session) {
                return [
                    'type' => 'simulator_completed',
                    'user' => $session->user,
                    'description' => "Completed simulator: {$session->simulator->title}",
                    'date' => $session->completed_at,
                    'data' => [
                        'simulator' => $session->simulator->title,
                        'score' => $session->score,
                        'time_spent' => $session->time_spent,
                    ],
                ];
            });

        // Merge and sort by date
        return $progressLogs->merge($simulatorSessions)
            ->sortByDesc('date')
            ->values();
    }

    /**
     * Get all teams for partner
     */
    public function getPartnerTeams(Partner $partner, array $filters): Collection
    {
        $query = CaseApplication::whereHas('case', function ($q) use ($partner) {
            $q->where('partner_id', $partner->id);
        })->accepted();

        // Apply case filter
        $caseId = FilterHelper::getIntegerFilter($filters['case_id'] ?? null);
        if ($caseId) {
            $query->where('case_id', $caseId);
        }

        // Apply status filter (for future when we have team-specific statuses)
        $status = FilterHelper::getStringFilter($filters['status'] ?? null);
        if ($status) {
            $query->withStatus($status);
        }

        return $query->with([
            'case',
            'leader',
            'teamMembers.user',
        ])->latest()->get();
    }
}
