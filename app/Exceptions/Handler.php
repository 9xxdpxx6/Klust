<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Логируем все исключения
            Log::error('Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
            ]);
        });

        // Обработка ошибок для Inertia запросов
        $this->renderable(function (Throwable $e, Request $request) {
            // Логируем ошибку перед обработкой
            Log::error('Inertia error rendering', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'is_inertia' => $request->header('X-Inertia') !== null,
            ]);

            // Проверяем, является ли запрос Inertia запросом
            if (!$request->header('X-Inertia')) {
                return null; // Используем стандартную обработку Laravel
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
            } elseif ($e instanceof ValidationException) {
                // Validation exceptions обрабатываются стандартным способом
                return null;
            }

            // Рендерим Inertia страницу ошибки без редиректа
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
