<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Services\BadgeService;
use Inertia\Inertia;
use Inertia\Response;

class BadgesController extends Controller
{
    public function __construct(
        private BadgeService $badgeService
    ) {
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Бейджи студента
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Получить полученные бейджи
        $badges = $this->badgeService->getStudentBadges($user);

        // Получить условия получения следующих бейджей
        $upcoming = $this->badgeService->getUpcomingBadges($user);

        return Inertia::render('Client/Student/Badges/Index', [
            'earnedBadges' => $badges,
            'upcomingBadges' => $upcoming,
            'currentPoints' => $user->studentProfile?->total_points ?? 0,
        ]);
    }
}
