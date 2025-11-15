<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $roles = Role::pluck('name')->toArray();

        // Получаем список курсов для фильтра
        $courses = User::whereNotNull('course')
            ->distinct()
            ->pluck('course')
            ->sort()
            ->values()
            ->toArray();

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
                $query->with(['case', 'case.partner', 'status']);
            },
            'caseTeamMembers' => function ($query) {
                $query->with(['application', 'application.case', 'application.status', 'application.leader']);
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
        $roles = Role::pluck('name')->toArray();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
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
                    $nextNumber = (int)$matches[1] + 1;
                } else {
                    $nextNumber = 1000001;
                }

                $kubgtu_id = 'STU' . $nextNumber;
            }

            $user = User::create([
                'kubgtu_id' => $kubgtu_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'avatar' => null, // Пока null, обновим после загрузки файла
                'course' => $request->course,
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

            $message = $kubgtu_id
                ? "Пользователь успешно создан с ID: {$kubgtu_id}"
                : 'Пользователь успешно создан';

            return redirect()->route('admin.users.index')
                ->with('success', $message);
        });
    }

    public function edit(User $user)
    {
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

    public function update(UpdateUserRequest $request, User $user)
    {
        // Подготовка данных для обновления
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'course' => $request->course,
        ];

        // Обновляем пароль только если он указан
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
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

        // Обновляем роль если она изменилась
        if ($request->role) {
            $currentRole = $user->roles->first()?->name ?? null;
            if ($request->role !== $currentRole) {
                $user->syncRoles([$request->role]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно обновлен.');
    }

    public function destroy(User $user)
    {
        // Нельзя удалить самого себя
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Вы не можете удалить свой собственный аккаунт.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален.');
    }
}
