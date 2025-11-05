<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\Profile\UpdateRequest;
use App\Services\UserService;
use App\Services\FileService;
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
        $user = auth()->user()->load(['partnerProfile', 'partner']);

        return Inertia::render('Client/Partner/Profile/Index', [
            'user' => $user,
        ]);
    }

    /**
     * Форма редактирования профиля
     */
    public function edit(): Response
    {
        $user = auth()->user()->load(['partnerProfile', 'partner']);

        return Inertia::render('Client/Partner/Profile/Edit', [
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

        // Обработать загрузку логотипа, если есть
        if ($request->hasFile('logo')) {
            $data['logo'] = $this->fileService->storeLogo($request->file('logo'));
        }

        // Обновить профиль партнера
        $this->userService->updatePartnerProfile($user, $data);

        return redirect()
            ->route('partner.profile.show')
            ->with('success', 'Профиль успешно обновлен');
    }
}

