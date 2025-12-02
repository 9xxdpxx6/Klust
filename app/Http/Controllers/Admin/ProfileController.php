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
        $this->middleware(['auth', 'role:admin|teacher']);
    }

    /**
     * Просмотр профиля
     */
    public function show(): Response
    {
        $user = auth()->user();

        return Inertia::render('Admin/Profile/Index', [
            'user' => $user,
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

        // Обработать загрузку аватара, если есть
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->fileService->storeAvatar($request->file('avatar'));
        }

        // Обновить профиль администратора
        $this->userService->updateProfile($user, $data);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Профиль успешно обновлен');
    }
}
