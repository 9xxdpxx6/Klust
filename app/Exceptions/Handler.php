<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Исключения, которые не нужно логировать.
     * ValidationException - это нормальная валидация форм, не ошибка.
     * NotFoundHttpException (404) - нормальные запросы несуществующих страниц.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
        NotFoundHttpException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Фильтруем исключения, которые не нужно логировать
        $this->reportable(function (Throwable $e) {
            $request = request();
            
            // Исключаем .well-known запросы (Chrome DevTools и другие инструменты)
            if ($request && str_contains($request->path(), '.well-known')) {
                return false;
            }

            // Не логируем 404 ошибки (нормальные запросы несуществующих страниц)
            if ($e instanceof NotFoundHttpException) {
                return false;
            }

            // Не логируем ValidationException (нормальная валидация форм)
            if ($e instanceof ValidationException) {
                return false;
            }
        });

        // Обработка ошибок для всех запросов (включая прямые переходы и перезагрузки)
        $this->renderable(function (Throwable $e, Request $request) {
            // Пропускаем обработку для .well-known запросов
            if (str_contains($request->path(), '.well-known')) {
                return null;
            }

            // Validation exceptions обрабатываются стандартным способом
            if ($e instanceof ValidationException) {
                return null;
            }

            $statusCode = 500;
            $errorPage = 'Errors/500';

            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
                $errorPage = match ($statusCode) {
                    403 => 'Errors/403',
                    404 => 'Errors/404',
                    419 => 'Errors/403', // CSRF token mismatch
                    500 => 'Errors/500',
                    503 => 'Errors/503',
                    default => 'Errors/500',
                };
            } elseif ($e instanceof AuthorizationException) {
                // Для AuthorizationException всегда возвращаем 403 без редиректа
                $statusCode = 403;
                $errorPage = 'Errors/403';
            } elseif ($e instanceof ModelNotFoundException) {
                $statusCode = 404;
                $errorPage = 'Errors/404';
            } elseif ($e instanceof TokenMismatchException) {
                $statusCode = 419;
                $errorPage = 'Errors/403';
            }

            // Рендерим Inertia страницу ошибки для всех запросов
            // Это работает как для Inertia запросов, так и для прямых переходов/перезагрузок
            // Важно: используем toResponse() и setStatusCode() для правильной установки статуса
            $response = Inertia::render($errorPage, [
                'status' => $statusCode,
                'message' => $e->getMessage(),
            ])->toResponse($request);
            
            // Устанавливаем статус код и заголовки, чтобы предотвратить редирект
            $response->setStatusCode($statusCode);
            
            return $response;
        });
    }
}
