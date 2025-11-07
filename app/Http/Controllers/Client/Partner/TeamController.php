<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Models\CaseApplication;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService
    ) {
        $this->middleware(['auth', 'role:partner']);
    }

    /**
     * Все команды партнера
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Получить фильтры
        $filters = [
            'case_id' => $request->input('case_id'),
            'status' => $request->input('status'),
        ];

        // Получить все команды партнера
        $teams = $this->teamService->getPartnerTeams($partner, $filters);

        // Загрузить кейсы и участников команд
        $teams->load(['case.partner', 'leader', 'teamMembers.user']);

        return Inertia::render('Client/Partner/Teams/Index', [
            'teams' => $teams,
        ]);
    }

    /**
     * Детали команды
     */
    public function show(CaseApplication $application): Response
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Проверить права (кейс принадлежит партнеру)
        if ($application->case->partner_id !== $partner->id) {
            abort(403);
        }

        // Загрузить команду со всеми участниками
        $application->load(['leader', 'teamMembers.user', 'case']);

        // Получить прогресс команды
        $progress = $this->teamService->getTeamProgress($application);

        // Получить историю активности
        $activityHistory = $this->teamService->getTeamActivityHistory($application);

        return Inertia::render('Client/Partner/Team/Show', [
            'team' => $application,
            'progress' => $progress,
            'activityHistory' => $activityHistory,
        ]);
    }
}

