<?php

use App\Http\Middleware\BindCompanyContext;
use App\Http\Middleware\EnsureApiClientIsAllowed;
use App\Http\Middleware\EnsureAuthenticationSessionIsActive;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active.session' => EnsureAuthenticationSessionIsActive::class,
            'api.client' => EnsureApiClientIsAllowed::class,
            'company.context' => BindCompanyContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'validation_error',
                    'message' => $exception->getMessage(),
                    'fields' => $exception->errors(),
                ],
            ], $exception->status);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'unauthenticated',
                    'message' => 'Authentication is required.',
                ],
            ], 401);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $exception->getStatusCode();
            $requestId = (string) Str::uuid();
            $codes = [
                403 => 'forbidden',
                404 => 'not_found',
                409 => 'conflict',
                419 => 'session_expired',
                422 => 'unprocessable_entity',
                429 => 'too_many_requests',
                503 => 'service_unavailable',
            ];

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $codes[$status] ?? 'http_error',
                    'message' => $exception->getMessage() ?: match ($status) {
                        403 => 'You are not allowed to perform this operation.',
                        404 => 'The requested resource was not found.',
                        429 => 'Too many requests. Try again later.',
                        503 => 'The service is temporarily unavailable.',
                        default => 'The request could not be completed.',
                    },
                    'request_id' => $requestId,
                ],
            ], $status, $exception->getHeaders());
        });
    })->create();
