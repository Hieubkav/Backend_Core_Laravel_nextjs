<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| API endpoints for version 1
|
*/

Route::middleware('api')->group(function () {
    // Public auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.updateProfile');
            Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.changePassword');
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        });

        // Admin only routes
        Route::middleware('admin')->group(function () {
            Route::apiResource('users', UserController::class);
        });
    });
});
