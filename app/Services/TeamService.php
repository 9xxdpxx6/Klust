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
        // Требуемые навыки для кейса (из таблицы case_skills)
        $caseSkillIds = $application->case->skills->pluck('id')->toArray();
        
        // Собираем все навыки всех участников команды
        $teamAllSkillIds = collect();
        foreach ($allMembers as $member) {
            $memberSkillIds = $member->skills()->pluck('skills.id');
            $teamAllSkillIds = $teamAllSkillIds->merge($memberSkillIds);
        }
        $teamAllSkillIds = $teamAllSkillIds->unique();
        
        // Считаем покрытие: сколько из требуемых навыков есть у команды (хотя бы у одного участника)
        $coveredSkillIds = array_intersect($caseSkillIds, $teamAllSkillIds->toArray());
        $coveredSkillsCount = count($coveredSkillIds);
        $requiredSkillsCount = count($caseSkillIds);

        // Calculate days remaining until deadline
        $daysRemaining = null;
        if ($application->case->deadline) {
            $now = now();
            $deadline = \Carbon\Carbon::parse($application->case->deadline);
            $daysRemaining = max(0, $now->diffInDays($deadline, false));
        }

        // Progress based only on activity (skills don't change during case execution)
        // Activity score represents team's activity level
        // No overall progress percentage - skills are static
        $overallProgress = 0; // Not used anymore - skills don't change

        // Calculate activity score based on total skill points
        $activityScore = round($averageSkillPoints / 10); // Simplified: points / 10

        return [
            'team_size' => $allMembers->count(),
            'total_skill_points' => $totalSkillPoints,
            'average_skill_points' => $averageSkillPoints,
            'matching_skills_count' => $coveredSkillsCount,
            'case_required_skills' => $requiredSkillsCount,
            'overall' => $overallProgress,
            'activity_score' => $activityScore,
            'days_remaining' => $daysRemaining ?? 0,
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
     * Get team activity history (only related to this case)
     */
    public function getTeamActivityHistory(CaseApplication $application): Collection
    {
        // Get all team member IDs
        $teamMemberIds = $application->teamMembers->pluck('user_id')->push($application->leader_id);

        $activities = collect();

        // 1. Get progress logs related to THIS application (applied_to_case, joined_team)
        $applicationLogs = ProgressLog::whereIn('user_id', $teamMemberIds)
            ->where('loggable_type', CaseApplication::class)
            ->where('loggable_id', $application->id)
            ->with('user')
            ->latest()
            ->get()
            ->map(function ($log) {
                $userName = $log->user->name;
                $description = match ($log->action) {
                    'applied_to_case' => "{$userName} подал(а) заявку на кейс",
                    'joined_team' => "{$userName} присоединился(ась) к команде",
                    default => "{$userName}: получено {$log->points_earned} баллов",
                };

                if ($log->points_earned > 0 && !str_contains($description, (string)$log->points_earned)) {
                    $description .= " ({$log->points_earned} баллов)";
                }

                return [
                    'id' => $log->id,
                    'type' => $log->action ?? 'progress',
                    'user' => $log->user,
                    'description' => $description,
                    'date' => $log->created_at,
                    'created_at' => $log->created_at,
                    'data' => [
                        'action' => $log->action,
                        'points' => $log->points_earned,
                    ],
                ];
            });

        $activities = $activities->merge($applicationLogs);

        // 2. Get simulator sessions completed by team members for THIS case's simulator only
        if ($application->case->simulator_id) {
        $simulatorSessions = SimulatorSession::whereIn('user_id', $teamMemberIds)
                ->where('simulator_id', $application->case->simulator_id)
            ->whereNotNull('completed_at')
            ->with(['user', 'simulator'])
                ->latest('completed_at')
            ->get()
            ->map(function ($session) {
                    $userName = $session->user->name;
                    $simulatorTitle = $session->simulator->title;
                    
                return [
                        'id' => $session->id,
                    'type' => 'simulator_completed',
                    'user' => $session->user,
                        'description' => "{$userName} завершил(а) симулятор: {$simulatorTitle}",
                    'date' => $session->completed_at,
                        'created_at' => $session->completed_at,
                    'data' => [
                            'simulator' => $simulatorTitle,
                        'score' => $session->score,
                        'time_spent' => $session->time_spent,
                    ],
                ];
            });

            $activities = $activities->merge($simulatorSessions);
        }

        // Удаляем дубликаты (если есть и session и log для одного события)
        $activities = $activities->unique(function ($item) {
            if ($item['type'] === 'simulator_completed') {
                return $item['type'].'_'.$item['user']['id'].'_'.$item['date']->format('Y-m-d H:i:s');
            }
            return $item['type'].'_'.$item['id'];
        });

        return $activities->sortByDesc('date')->values();
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
