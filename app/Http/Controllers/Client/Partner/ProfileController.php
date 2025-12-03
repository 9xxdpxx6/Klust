<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Profile\UpdateRequest;
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
        $this->middleware(['auth', 'role:partner']);
    }

    /**
     * Профиль компании
     */
    public function show(): Response
    {
        try {
            $user = auth()->user()->load(['partnerProfile', 'partner']);

            return Inertia::render('Client/Partner/Profile/Index', [
                'user' => $user,
                'partnerProfile' => $user->partnerProfile,
                'partner' => $user->partner,
            ]);
        } catch (\Exception $e) {
            $user = auth()->user();

            return Inertia::render('Client/Partner/Profile/Index', [
                'user' => $user,
                'partnerProfile' => $user->partnerProfile ?? null,
                'partner' => $user->partner ?? null,
                'error' => 'Ошибка при загрузке профиля: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Форма редактирования профиля
     */
    public function edit(): Response
    {
        try {
            $user = auth()->user()->load(['partnerProfile', 'partner']);

            return Inertia::render('Client/Partner/Profile/Edit', [
                'user' => $user,
                'partnerProfile' => $user->partnerProfile,
                'partner' => $user->partner,
            ]);
        } catch (\Exception $e) {
            $user = auth()->user();

            return Inertia::render('Client/Partner/Profile/Edit', [
                'user' => $user,
                'partnerProfile' => $user->partnerProfile ?? null,
                'partner' => $user->partner ?? null,
                'error' => 'Ошибка при загрузке формы: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Обновление профиля
     */
    public function update(UpdateRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();

            $data = $request->validated();

            // Обработать загрузку логотипа, если есть
            if ($request->hasFile('logo')) {
                $data['logo'] = $this->fileService->storeLogo($request->file('logo'));
            }

            // Обработать загрузку аватара, если есть
            if ($request->hasFile('avatar')) {
                $data['avatar'] = $request->file('avatar');
            }

            // Обновить профиль партнера
            $this->userService->updatePartnerProfile($user, $data);

            return redirect()
                ->route('partner.profile.show')
                ->with('success', 'Профиль успешно обновлен');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ошибка при обновлении профиля: '.$e->getMessage());
        }
    }

    /**
     * Удаление аватара
     */
    public function deleteAvatar(): RedirectResponse
    {
        $user = auth()->user();
        $this->userService->deleteUserAvatar($user);

        return redirect()
            ->route('partner.profile.show')
            ->with('success', 'Фотография удалена');
    }
}
