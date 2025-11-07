<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Models\CaseApplication;
use App\Services\TeamService;
use App\Services\CaseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService,
        private CaseService $caseService
    ) {
        $this->middleware(['auth', 'role:partner']);
    }

    /**
     * Все команды партнера
     */
    public function index(Request $request): Response
    {
        try {
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
        } catch (\Exception $e) {
            return redirect()
                ->route('partner.dashboard')
                ->with('error', 'Ошибка при загрузке команд: ' . $e->getMessage());
        }
    }

    /**
     * Детали команды
     */
    public function show(CaseApplication $application): Response
    {
        try {
            $user = auth()->user();
            $partner = $user->partner;

            // Загрузить кейс для проверки прав
            $application->load('case');

            // Проверить права (кейс принадлежит партнеру)
            $this->caseService->ensureCaseBelongsToPartner($application->case, $partner);

            // Загрузить команду со всеми участниками
            $application->load(['leader', 'teamMembers.user', 'case']);

            // Получить прогресс команды
            $progress = $this->teamService->getTeamProgress($application);

            // Получить историю активности
            $activityHistory = $this->teamService->getTeamActivityHistory($application);

            return Inertia::render('Client/Partner/Teams/Show', [
                'team' => $application,
                'progress' => $progress,
                'activityHistory' => $activityHistory,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('partner.teams.index')
                ->with('error', 'Ошибка при загрузке команды: ' . $e->getMessage());
        }
    }
}

