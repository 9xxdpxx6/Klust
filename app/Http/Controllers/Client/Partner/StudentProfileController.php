<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StudentProfileController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {
        $this->middleware(['auth', 'role:partner']);
    }

    /**
     * Просмотр профиля студента партнером
     */
    public function show(User $student): Response|RedirectResponse
    {
        try {
            // Проверить права доступа
            $this->authorize('viewStudentProfile', $student);

            // Загрузить данные студента
            $student->load([
                'studentProfile.faculty',
                'skills' => function ($query) {
                    $query->withPivot('level', 'points_earned')
                        ->orderBy('name');
                },
                'caseApplications' => function ($query) {
                    $acceptedStatusId = ApplicationStatus::getIdByName('accepted');
                    $completedStatusId = ApplicationStatus::getIdByName('completed');
                    $query->whereIn('status_id', [$acceptedStatusId, $completedStatusId])
                        ->with(['case.partner', 'status'])
                        ->orderBy('created_at', 'desc');
                },
            ]);

            // Получить достижения с правильной обработкой icon_path
            $badges = $student->badges()
                ->withPivot('earned_at')
                ->orderByDesc('pivot_earned_at')
                ->get()
                ->map(function ($badge) {
                    // Если icon начинается с 'pi-' или 'fa-', это класс иконки, а не путь к файлу
                    $iconPath = null;
                    if ($badge->icon && ! str_starts_with($badge->icon, 'pi-') && ! str_starts_with($badge->icon, 'fa-')) {
                        $iconPath = '/storage/'.$badge->icon;
                    }

                    return [
                        'id' => $badge->id,
                        'name' => $badge->name,
                        'description' => $badge->description,
                        'icon' => $badge->icon,
                        'icon_path' => $iconPath,
                        'required_points' => $badge->required_points,
                        'pivot' => [
                            'earned_at' => $badge->pivot->earned_at,
                        ],
                    ];
                });

            // Получить статистику
            $statistics = [
                'total_points' => $student->studentProfile?->total_points ?? 0,
                'skills_count' => $student->skills->count(),
                'badges_count' => $badges->count(),
                'completed_cases' => $student->caseApplications()
                    ->whereHas('status', function ($q) {
                        $q->where('name', 'completed');
                    })
                    ->count(),
                'active_cases' => $student->caseApplications()
                    ->whereHas('status', function ($q) {
                        $q->where('name', 'accepted');
                    })
                    ->count(),
            ];

            // Получить только завершенные кейсы для отображения
            $completedCases = $student->caseApplications()
                ->whereHas('status', function ($q) {
                    $q->where('name', 'completed');
                })
                ->with(['case.partner', 'status'])
                ->orderBy('created_at', 'desc')
                ->get();

            return Inertia::render('Client/Partner/Students/Show', [
                'student' => $student,
                'studentProfile' => $student->studentProfile,
                'statistics' => $statistics,
                'skills' => $student->skills,
                'badges' => $badges,
                'completedCases' => $completedCases,
            ]);
        } catch (AuthorizationException $e) {
            return redirect()
                ->route('partner.teams.index')
                ->with('error', __('auth.unauthorized'));
        } catch (\Exception $e) {
            return redirect()
                ->route('partner.teams.index')
                ->with('error', 'Ошибка при загрузке профиля студента: '.$e->getMessage());
        }
    }
}

