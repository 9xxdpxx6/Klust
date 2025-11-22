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
        $query = User::with('roles')
            ->select(['id', 'name', 'email', 'kubgtu_id', 'course', 'avatar', 'email_verified_at', 'created_at']);

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

        // Получаем список курсов для фильтра
        $courses = User::whereNotNull('course')
            ->distinct()
            ->pluck('course')
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

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'stats' => $stats,
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
            'course' => $request->course,
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
