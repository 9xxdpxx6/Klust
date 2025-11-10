<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

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
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CaseApplication::class);

        $user = $request->user();
        $partner = $user->partner;

        if (! $partner) {
            abort(403, 'Partner access required');
        }

        $teams = $this->teamService->getPartnerTeams($partner, $request->only(['case_id', 'status']));

        return Inertia::render('Partner/Teams/Index', [
            'teams' => $teams,
            'filters' => $request->only(['case_id', 'status']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(CaseApplication $application): Response
    {
        $this->authorize('view', $application);

        // Verify that the application belongs to the partner's cases
        $user = auth()->user();
        $partner = $user->partner;

        if (! $partner || $application->case->partner_id !== $partner->id) {
            abort(403);
        }

        $teamProgress = $this->teamService->getTeamProgress($application);
        $teamActivity = $this->teamService->getTeamActivityHistory($application);

        return Inertia::render('Partner/Teams/Show', [
            'application' => $application->load(['case', 'leader', 'teamMembers.user']),
            'teamProgress' => $teamProgress,
            'teamActivity' => $teamActivity,
        ]);
    }
}