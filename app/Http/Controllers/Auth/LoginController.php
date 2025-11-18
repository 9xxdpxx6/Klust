<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Показать форму входа
     */
    public function show(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Обработать запрос на вход
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Редирект на соответствующий dashboard по роли
            if ($user->hasRole(['admin', 'teacher'])) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('student')) {
                return redirect()->route('student.dashboard');
            } elseif ($user->hasRole('partner')) {
                return redirect()->route('partner.dashboard');
            }

            // Если роль не определена, редирект на главную
            return redirect()->route('dashboard');
        }

        // Если авторизация не удалась
        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->onlyInput('email');
    }
}
