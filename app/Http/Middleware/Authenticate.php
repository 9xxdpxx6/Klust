<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Для Inertia запросов и JSON не делаем редирект, позволяем исключению проброситься в Handler
        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return null;
        }

        return route('login');
    }
}
