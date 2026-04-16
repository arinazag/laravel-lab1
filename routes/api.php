<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Группа маршрутов авторизации
Route::prefix('auth')->group(function () {

    // Открытые маршруты (без токена)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Защищенные маршруты (требуют токен)
    Route::middleware(['auth.token'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/out', [AuthController::class, 'out']);
        Route::get('/tokens', [AuthController::class, 'tokens']);
        Route::post('/out_all', [AuthController::class, 'outAll']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});