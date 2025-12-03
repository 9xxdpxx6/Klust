<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UserFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Список пользователей с фильтрацией и пагинацией
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // Получаем параметры фильтрации из запроса
        $filters = [
            'search' => $request->input('search', ''),
            'role' => $request->input('role', ''),
            'status' => $request->input('status', ''),
            'course' => $request->input('course', ''),
            'perPage' => $request->input('perPage', 25),
        ];

        // Строим запрос
        $query = User::with(['roles', 'studentProfile'])
            ->select(['id', 'name', 'email', 'kubgtu_id', 'avatar', 'email_verified_at', 'created_at']);

        // Применяем фильтры
        $userFilter = new UserFilter($filters);
        $query = $userFilter->apply($query);

        // Получаем параметры пагинации
        $paginationParams = $userFilter->getPaginationParams();

        // Сортировка и пагинация
        $users = $query->paginate($paginationParams['per_page'])
            ->withQueryString();

        // Получаем список ролей для фильтра
        $roles = Role::pluck('name')->toArray();

        // Получаем переводы ролей
        $roleTranslations = [];
        foreach ($roles as $roleName) {
            $roleTranslations[$roleName] = trans("roles.{$roleName}", [], 'ru');
        }

        // Цвета для курсов (из DashboardService)
        $courseColors = [
            1 => '#22C55E', // зеленый
            2 => '#FDE047', // желтый
            3 => '#F97316', // оранжевый
            4 => '#EF4444', // красный
            5 => '#3B82F6', // синий
            6 => '#EAB308', // желтый
        ];

        // Цвета для ролей
        $roleColors = [
            'admin' => '#DC2626',   // красный
            'partner' => '#2563EB',  // синий
            'student' => '#10B981', // зеленый
            'teacher' => '#F59E0B',  // янтарный
        ];

        // Получаем список курсов для фильтра
        $courses = User::role('student')
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->whereNotNull('student_profiles.course')
            ->distinct()
            ->pluck('student_profiles.course')
            ->sort()
            ->values()
            ->toArray();

        // Получаем общую статистику (независимо от фильтров и пагинации)
        $statistics = [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'students' => User::role('student')->count(),
            'partners' => User::role('partner')->count(),
            'teachers' => User::role('teacher')->count(),
            'admins' => User::role('admin')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters, // Всегда передаем filters
            'roles' => $roles,
            'roleTranslations' => $roleTranslations,
            'roleColors' => $roleColors,
            'courseColors' => $courseColors,
            'courses' => $courses,
            'statistics' => $statistics,
        ]);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        // Загружаем все связи пользователя
        $user->load([
            'roles',
            'studentProfile.faculty',
            'partnerProfile',
            'teacherProfile',
            'skills' => function ($query) {
                $query->withPivot('level', 'points_earned');
            },
            'badges',
            'simulatorSessions',
            'caseApplications' => function ($query) {
                $query->with(['case', 'case.partner', 'status']);
            },
            'caseTeamMembers' => function ($query) {
                $query->with(['application', 'application.case', 'application.status', 'application.leader']);
            },
            'notifications',
            'progressLogs',
            'partner',
        ]);

        $stats = [
            'skills_count' => $user->skills->count(),
            'badges_count' => $user->badges->count(),
            'simulator_sessions_count' => $user->simulatorSessions->count(),
            'case_applications_count' => $user->caseApplications->count(),
            'team_memberships_count' => $user->caseTeamMembers->count(),
            'notifications_count' => $user->notifications->count(),
        ];

        // Проверка связанных данных, которые могут помешать удалению
        $activeApplicationsCount = $user->caseApplications()
            ->whereHas('status', function ($q) {
                $q->whereIn('name', ['pending', 'accepted']);
            })
            ->count();

        $activeTeamMembershipsCount = $user->caseTeamMembers()
            ->whereHas('application', function ($q) {
                $q->whereHas('status', function ($statusQ) {
                    $statusQ->whereIn('name', ['pending', 'accepted']);
                });
            })
            ->count();

        $activeCasesCount = 0;
        if ($user->hasRole('partner') && $user->partner) {
            $activeCasesCount = $user->partner->cases()
                ->where('status', 'active')
                ->count();
        }

        $hasBlockingData = $activeApplicationsCount > 0 || $activeTeamMembershipsCount > 0 || $activeCasesCount > 0;

        $blockingData = [
            'active_applications_count' => $activeApplicationsCount,
            'active_team_memberships_count' => $activeTeamMembershipsCount,
            'active_cases_count' => $activeCasesCount,
            'has_blocking_data' => $hasBlockingData,
        ];

        // Transform badges to include icon_path
        $user->badges->transform(function ($badge) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'icon_path' => $badge->icon_path,
                'required_points' => $badge->required_points,
                'pivot' => $badge->pivot,
            ];
        });

        // Получаем переводы ролей
        $roles = Role::pluck('name')->toArray();
        $roleTranslations = [];
        foreach ($roles as $roleName) {
            $roleTranslations[$roleName] = trans("roles.{$roleName}", [], 'ru');
        }

        // Цвета для курсов (из DashboardService)
        $courseColors = [
            1 => '#22C55E',
            2 => '#FDE047',
            3 => '#F97316',
            4 => '#EF4444',
            5 => '#3B82F6',
            6 => '#EAB308',
        ];

        // Цвета для ролей
        $roleColors = [
            'admin' => '#DC2626',
            'partner' => '#2563EB',
            'student' => '#10B981',
            'teacher' => '#F59E0B',
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'roleTranslations' => $roleTranslations,
            'roleColors' => $roleColors,
            'courseColors' => $courseColors,
            'stats' => $stats,
            'blockingData' => $blockingData,
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::pluck('name')->toArray();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', User::class);

        // Используем транзакцию для избежания race condition
        return DB::transaction(function () use ($request) {
            // Находим максимальный существующий номер
            $kubgtu_id = null;

            // Генерируем kubgtu_id только для студентов
            if ($request->role === 'student') {
                $lastUser = User::where('kubgtu_id', 'like', 'STU%')
                    ->orderByRaw('CAST(SUBSTRING(kubgtu_id, 4) AS UNSIGNED) DESC')
                    ->first();

                if ($lastUser && preg_match('/STU(\d+)/', $lastUser->kubgtu_id, $matches)) {
                    $nextNumber = (int) $matches[1] + 1;
                } else {
                    $nextNumber = 1000001;
                }

                $kubgtu_id = 'STU'.$nextNumber;
            }

            $user = User::create([
                'kubgtu_id' => $kubgtu_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'avatar' => null, // Пока null, обновим после загрузки файла
                'email_verified_at' => now(),
            ]);

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->update(['avatar' => $avatarPath]);
            }

            // Назначаем роль пользователю
            if ($request->role) {
                $user->assignRole($request->role);
            }

            // Создаем профиль студента с course, если роль - student
            if ($request->role === 'student') {
                \App\Models\StudentProfile::create([
                    'user_id' => $user->id,
                    'faculty_id' => $request->faculty_id ?? null,
                    'course' => $request->course ?? null,
                    'group' => $request->group ?? null,
                    'specialization' => $request->specialization ?? null,
                    'total_points' => 0,
                ]);
            }

            $message = $kubgtu_id
                ? "Пользователь успешно создан с ID: {$kubgtu_id}"
                : 'Пользователь успешно создан';

            return redirect()->route('admin.users.index')
                ->with('success', $message);
        });
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::pluck('name')->toArray();

        // Загружаем текущую роль пользователя
        $user->load('roles');
        $currentRole = $user->roles->first()->name ?? '';

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'currentRole' => $currentRole,
            'roles' => $roles,
        ]);
    }

    public function update(UpdateRequest $request, User $user)
    {
        $this->authorize('update', $user);

        // Подготовка данных для обновления
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Обновляем пароль только если он указан и отличается от текущего
        if ($request->filled('password')) {
            if (! Hash::check($request->password, $user->password)) {
                $updateData['password'] = Hash::make($request->password);
            }
        }

        // Обновляем аватар если указан
        if ($request->hasFile('avatar')) {
            // Удаляем старый аватар если есть
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
        }

        // Обновляем пользователя
        $user->update($updateData);

        // Обновляем профиль студента, если пользователь - студент
        if ($user->hasRole('student') && $user->studentProfile) {
            $profileData = [];
            if ($request->has('faculty_id')) {
                $profileData['faculty_id'] = $request->faculty_id;
            }
            if ($request->has('group')) {
                $profileData['group'] = $request->group;
            }
            if ($request->has('course')) {
                $profileData['course'] = $request->course;
            }
            if ($request->has('specialization')) {
                $profileData['specialization'] = $request->specialization;
            }
            if (!empty($profileData)) {
                $user->studentProfile->update($profileData);
            }
        }

        // Роль не может быть изменена при редактировании пользователя
        // Она устанавливается только при создании и не должна обновляться здесь

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно обновлен.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален.');
    }
}
