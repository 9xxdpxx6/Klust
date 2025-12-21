<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Кастомизируем письмо верификации email
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new \App\Mail\VerifyEmailMail($url, $notifiable))
                ->to($notifiable->getEmailForVerification());
        });
    }
}
