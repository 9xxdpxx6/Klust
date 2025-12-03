<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\Profile\UpdateRequest;
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
        $this->middleware(['auth', 'role:student']);
    }

    /**
     * Просмотр профиля
     */
    public function show(): Response
    {
        $user = auth()->user()->load('studentProfile');

        return Inertia::render('Client/Student/Profile/Index', [
            'user' => $user,
        ]);
    }

    /**
     * Форма редактирования профиля
     */
    public function edit(): Response
    {
        $user = auth()->user()->load('studentProfile');

        return Inertia::render('Client/Student/Profile/Edit', [
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

        // Обработать загрузку аватара, если есть
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar');
        }

        // Обновить профиль студента
        $this->userService->updateStudentProfile($user, $data);

        return redirect()
            ->route('student.profile.show')
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
            ->route('student.profile.show')
            ->with('success', 'Фотография удалена');
    }
}
