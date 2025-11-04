<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;


class UsersController extends Controller
{
    /**
     * Список пользователей с фильтрацией и пагинацией
     */
    public function index(Request $request)
    {
        // Получаем параметры фильтрации из запроса
        $filters = [
            'search' => $request->input('search', ''),
            'role' => $request->input('role', ''),
            'status' => $request->input('status', ''),
            'course' => $request->input('course', ''),
            'perPage' => $request->input('perPage', 15),
        ];

        // Строим запрос
        $query = User::with('roles')
            ->select(['id', 'name', 'email', 'kubgtu_id', 'course', 'avatar', 'email_verified_at', 'created_at']);

        // Поиск по имени, email или kubgtu_id
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('kubgtu_id', 'like', "%{$search}%");
            });
        }

        // Фильтрация по роли
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Фильтрация по статусу верификации email
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($filters['status'] === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Фильтрация по курсу
        if (!empty($filters['course'])) {
            $query->where('course', $filters['course']);
        }

        // Сортировка и пагинация
        $users = $query->orderBy('created_at', 'desc')
            ->paginate($filters['perPage'])
            ->withQueryString();

        // Получаем список ролей для фильтра
        $roles = Role::pluck('name');

        // Получаем список курсов для фильтра
        $courses = User::whereNotNull('course')
            ->distinct()
            ->pluck('course')
            ->sort();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters, // Всегда передаем filters
            'roles' => $roles,
            'courses' => $courses,
        ]);
    }

    public function show(User $user)
    {
        // Загружаем все связи пользователя
        $user->load([
            'roles',
            'studentProfile',
            'partnerProfile',
            'teacherProfile',
            'skills' => function ($query) {
                $query->withPivot('level', 'points_earned');
            },
            'badges',
            'simulatorSessions',
            'caseApplications' => function ($query) {
                $query->with(['case', 'case.partner']);
            },
            'caseTeamMembers' => function ($query) {
                $query->with(['application', 'application.case']);
            },
            'notifications',
            'progressLogs',
            'partner'
        ]);

        $stats = [
            'skills_count' => $user->skills->count(),
            'badges_count' => $user->badges->count(),
            'simulator_sessions_count' => $user->simulatorSessions->count(),
            'case_applications_count' => $user->caseApplications->count(),
            'team_memberships_count' => $user->caseTeamMembers->count(),
            'notifications_count' => $user->notifications->count(),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $roles = Role::pluck('name');

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        // Создаем пользователя
        $user = User::create([
            'kubgtu_id' => $request->kubgtu_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $request->avatar,
            'course' => $request->course,
            'email_verified_at' => $request->has('email_verified') ? now() : null,
        ]);

        // Назначаем роль пользователю
        if ($request->role) {
            $user->assignRole($request->role);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно создан.');
    }
}
