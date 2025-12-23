<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * Get general statistics for admin dashboard
     */
    public function getStatistics(): array
    {
        // Define time periods
        $lastMonth = Carbon::now()->subMonth();
        $lastWeek = Carbon::now()->subWeek();

        // Count students
        $totalStudents = User::role('student')->count();
        $newStudentsThisMonth = User::role('student')
            ->where('created_at', '>=', $lastMonth)
            ->count();

        // Count partners
        $totalPartners = User::role('partner')->count();
        $newPartnersThisMonth = User::role('partner')
            ->where('created_at', '>=', $lastMonth)
            ->count();

        // Count cases by status
        $activeCases = CaseModel::where('status', 'active')->count();
        $completedCases = CaseModel::where('status', 'completed')->count();
        $completedCasesThisMonth = CaseModel::where('status', 'completed')
            ->where('updated_at', '>=', $lastMonth)
            ->count();
        $draftCases = CaseModel::where('status', 'draft')->count();
        $totalCases = CaseModel::count();

        // Count applications
        $pendingApplications = CaseApplication::pending()->count();
        $acceptedApplications = CaseApplication::accepted()->count();
        $totalApplications = CaseApplication::count();

        // Recent activity
        $recentUsers = User::with('roles')->latest()->limit(10)->get();
        $recentCases = CaseModel::with('partnerUser.partnerProfile')->latest()->limit(10)->get();
        $completedCasesList = CaseModel::where('status', 'completed')
            ->latest('updated_at')
            ->limit(10)
            ->get();
        $recentApplications = CaseApplication::with(['leader', 'case'])
            ->latest()
            ->limit(10)
            ->get();

        return [
            'overview' => [
                'total_students' => $totalStudents,
                'new_students_this_month' => $newStudentsThisMonth,
                'total_partners' => $totalPartners,
                'new_partners_this_month' => $newPartnersThisMonth,
                'total_cases' => $totalCases,
                'active_cases' => $activeCases,
                'completed_cases' => $completedCases,
                'completed_cases_this_month' => $completedCasesThisMonth,
                'draft_cases' => $draftCases,
                'total_applications' => $totalApplications,
                'pending_applications' => $pendingApplications,
                'accepted_applications' => $acceptedApplications,
            ],
            'recent_activity' => [
                'users' => $recentUsers->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $this->translateRole($this->getPrimaryRole($user)),
                    'roles' => $user->roles->pluck('name')->map(fn ($role) => $this->translateRole($role))->toArray(),
                    'created_at' => $user->created_at?->toISOString(),
                ]),
                'cases' => $recentCases->map(fn ($case) => [
                    'id' => $case->id,
                    'title' => $case->title,
                    'partner' => $case->partnerUser?->partnerProfile?->company_name ?? $case->partnerUser?->name ?? 'Без партнера',
                    'status' => $case->status,
                    'created_at' => $case->created_at?->toISOString(),
                ]),
                'completed_cases' => $completedCasesList->map(fn ($case) => [
                    'id' => $case->id,
                    'title' => $case->title,
                    'partner' => $case->partnerUser?->partnerProfile?->company_name ?? $case->partnerUser?->name ?? 'Без партнера',
                    'status' => $case->status,
                    'completed_at' => $case->updated_at?->toISOString(),
                    'updated_at' => $case->updated_at?->toISOString(),
                ]),
                'applications' => $recentApplications->map(fn ($app) => [
                    'id' => $app->id,
                    'leader' => $app->leader?->name,
                    'case' => $app->case?->title,
                    'status' => $app->status,
                    'submitted_at' => $app->submitted_at?->toISOString(),
                ]),
            ],
            'charts' => [
                'cases_by_status' => [
                    'active' => $activeCases,
                    'completed' => $completedCases,
                    'draft' => $draftCases,
                    'archived' => CaseModel::where('status', 'archived')->count(),
                ],
                'applications_by_status' => [
                    'pending' => $pendingApplications,
                    'accepted' => $acceptedApplications,
                    'rejected' => CaseApplication::rejected()->count(),
                ],
            ],
        ];
    }

    /**
     * Get weekly statistics for charts
     */
    public function getWeeklyStatistics(): array
    {
        $days = [];
        $newUsers = [];
        $newApplications = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            // Форматируем дату на русском языке: день + сокращенное название месяца
            $monthIndex = (int) $date->format('n');
            $monthName = __('months.short', [], 'ru')[$monthIndex] ?? $date->format('M');
            $days[] = $date->format('d').' '.$monthName;

            $newUsers[] = User::whereDate('created_at', $date)->count();
            $newApplications[] = CaseApplication::whereDate('submitted_at', $date)->count();
        }

        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => __('dashboard.charts.new_users', [], 'ru'),
                    'data' => $newUsers,
                ],
                [
                    'label' => __('dashboard.charts.new_applications', [], 'ru'),
                    'data' => $newApplications,
                ],
            ],
        ];
    }

    /**
     * Get top skills by user count
     */
    public function getTopSkills(int $limit = 5): array
    {
        return Skill::withCount('users')
            ->orderByDesc('users_count')
            ->limit($limit)
            ->get()
            ->map(fn ($skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'count' => $skill->users_count,
            ])
            ->toArray();
    }

    /**
     * Get students distribution by course
     */
    public function getStudentsByCourse(): array
    {
        $distribution = User::role('student')
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->selectRaw('COALESCE(student_profiles.course, 0) as course, count(*) as count')
            ->groupBy('student_profiles.course')
            ->orderBy('student_profiles.course')
            ->get();

        $labels = $distribution->map(function ($item) {
            if ($item->course > 0) {
                return __('dashboard.course.course_label', ['number' => $item->course], 'ru');
            }

            return __('dashboard.course.not_specified', [], 'ru');
        })->toArray();
        $data = $distribution->pluck('count')->toArray();

        // Цвета для курсов (индекс массива = номер курса)
        $courseColors = [
            0 => '#374151', // Не указан - темно-серый
            1 => '#22C55E', // 1 курс - зеленый
            2 => '#FDE047', // 2 курс - желтый
            3 => '#F97316', // 3 курс - оранжевый
            4 => '#EF4444', // 4 курс - красный
            5 => '#3B82F6', // 5 курс - синий
            6 => '#EAB308', // 6 курс - золотисто-желтый
        ];

        // Формируем массив цветов в том же порядке, что и данные
        $backgroundColor = $distribution->map(function ($item) use ($courseColors) {
            return $courseColors[$item->course] ?? '#6B7280'; // Серый по умолчанию для неизвестных курсов
        })->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('dashboard.charts.students', [], 'ru'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
        ];
    }

    /**
     * Get primary role for user based on priority
     * Priority: admin > teacher > partner > student
     */
    private function getPrimaryRole(User $user): ?string
    {
        $roleNames = $user->roles->pluck('name')->toArray();

        if (empty($roleNames)) {
            return null;
        }

        // Определяем приоритет ролей
        $priority = [
            'admin' => 1,
            'teacher' => 2,
            'partner' => 3,
            'student' => 4,
        ];

        // Сортируем роли по приоритету и возвращаем самую приоритетную
        usort($roleNames, function ($a, $b) use ($priority) {
            $priorityA = $priority[$a] ?? 999;
            $priorityB = $priority[$b] ?? 999;

            return $priorityA <=> $priorityB;
        });

        return $roleNames[0];
    }

    /**
     * Translate role name to Russian using Laravel Translation
     */
    private function translateRole(?string $role): ?string
    {
        if (! $role) {
            return null;
        }

        return __("roles.{$role}", [], 'ru') ?: $role;
    }
}
