<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? (function () use ($request) {
                    $user = $request->user();
                    $userData = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                        'roles' => $user->getRoleNames(),
                        'avatar' => $user->avatar,
                    ];

                    // Загружаем partner_profile для партнеров
                    if ($user->hasRole('partner')) {
                        $user->load('partnerProfile');
                        if ($user->partnerProfile) {
                            $userData['partner_profile'] = [
                                'company_name' => $user->partnerProfile->company_name,
                                'contact_person' => $user->partnerProfile->contact_person,
                            ];
                        }
                    }

                    return $userData;
                })() : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'notifications' => [
                'unreadCount' => fn () => $request->user()
                    ? $request->user()->unreadNotifications->count()
                    : 0,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->route()?->getName() ?? $request->url(),
            ],
        ];
    }
}
