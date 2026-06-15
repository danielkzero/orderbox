<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => [
        'success' => true,
        'data' => ['status' => 'ok'],
    ]);

    Route::get('/ready', function () {
        try {
            DB::select('select 1');
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'service_unavailable',
                    'message' => 'Database connection is unavailable.',
                ],
            ], 503);
        }

        return [
            'success' => true,
            'data' => ['status' => 'ready'],
        ];
    });

    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/2fa/confirm', [AuthController::class, 'confirmTwoFactor'])->middleware('throttle:5,1');

        Route::middleware(['auth:sanctum', 'active.session', 'company.context'])->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });
});
