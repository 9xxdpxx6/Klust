<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profile\UpdateRequest;
use App\Services\FileService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private UserService $userService,
        private FileService $fileService
    ) {
        $this->middleware(['auth', 'role:admin|teacher|student|partner']);
    }

    /**
     * Просмотр профиля
     */
    public function show(): Response
    {
        $user = auth()->user();
        
        // Загружаем профили в зависимости от роли
        /** @var \App\Models\User $user */
        $user->load(['studentProfile.faculty', 'teacherProfile', 'partnerProfile', 'roles']);
        
        // Загружаем факультеты для студентов
        $faculties = [];
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('student')) {
            $faculties = \App\Models\Faculty::where('is_active', true)->orderBy('name')->get();
        }

        return Inertia::render('Admin/Profile/Index', [
            'user' => $user,
            'faculties' => $faculties,
        ]);
    }

    /**
     * Форма редактирования профиля
     */
    public function edit(): Response
    {
        $user = auth()->user();

        return Inertia::render('Admin/Profile/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Обновление профиля
     */
    public function update(UpdateRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        // Передаем файл аватара в сервис, если есть
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar');
        }

        // Обновить профиль
        $this->userService->updateProfile($user, $data);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Профиль успешно обновлен');
    }

    /**
     * Удаление аватара
     */
    public function deleteAvatar(): RedirectResponse
    {
        $user = auth()->user();
        $this->userService->deleteUserAvatar($user);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Фотография удалена');
    }
}
