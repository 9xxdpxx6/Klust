<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Filters\TeamFilter;
use App\Http\Controllers\Controller;
use App\Models\CaseApplication;
use App\Services\CaseService;
use App\Services\TeamService;
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

            if (! $partner) {
                return redirect()
                    ->route('partner.dashboard')
                    ->with('error', 'Партнер не найден');
            }

            $filters = $request->only([
                'search',
                'case_id',
                'status',
                'leader_id',
                'submitted_from',
                'submitted_to',
                'sort_by',
                'sort_order',
                'per_page',
            ]);

            $teamFilter = new TeamFilter($filters);

            $teamsQuery = CaseApplication::query()
                ->whereHas('case', function ($caseQuery) use ($partner): void {
                    $caseQuery->where('partner_id', $partner->id);
                })
                ->with(['case.partner', 'leader', 'teamMembers.user']);

            if (! $request->filled('status')) {
                $teamsQuery->accepted();
            }

            $pagination = $teamFilter->getPaginationParams();

            $teams = $teamFilter
                ->apply($teamsQuery)
                ->paginate($pagination['per_page'])
                ->withQueryString();

            return Inertia::render('Client/Partner/Teams/Index', [
                'teams' => $teams,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('partner.dashboard')
                ->with('error', 'Ошибка при загрузке команд: '.$e->getMessage());
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
            $this->authorize('view', $application->case);

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
                ->with('error', 'Ошибка при загрузке команды: '.$e->getMessage());
        }
    }
}
