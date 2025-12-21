<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    /**
     * Показать страницу с информацией о необходимости верификации email
     */
    public function notice(): Response|RedirectResponse
    {
        // Если email уже подтвержден, редиректим на dashboard
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard')
                ->with('success', 'Email успешно подтвержден!');
        }
        
        return Inertia::render('Auth/VerifyEmail');
    }

    /**
     * Обработать верификацию email
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard')
                ->with('success', 'Email успешно подтвержден!');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('dashboard')
            ->with('success', 'Email успешно подтвержден!');
    }

    /**
     * Повторно отправить письмо с подтверждением email
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        try {
            $user = $request->user();
            
            // Проверяем настройки почты перед отправкой
            $mailConfig = config('mail.default');
            if ($mailConfig === 'log') {
                return redirect()->route('verification.notice')
                    ->with('success', 'Письмо с подтверждением отправлено на ваш email. (Режим логирования)');
            }
            
            // Проверяем, что SMTP настройки заполнены
            $smtpHost = config('mail.mailers.smtp.host');
            $smtpUsername = config('mail.mailers.smtp.username');
            $smtpPassword = config('mail.mailers.smtp.password');
            
            if (empty($smtpHost) || empty($smtpUsername) || empty($smtpPassword)) {
                return redirect()->route('verification.notice')
                    ->with('error', 'Настройки SMTP неполные. Проверьте конфигурацию.');
            }
            
            $user->sendEmailVerificationNotification();
            
            return redirect()->route('verification.notice')
                ->with('success', 'Письмо с подтверждением отправлено на ваш email.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send verification email', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('verification.notice')
                ->with('error', 'Не удалось отправить письмо. Попробуйте позже или обратитесь в поддержку.');
        }
    }
}

