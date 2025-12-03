<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| API endpoints for version 1
|
*/

Route::middleware('api')->group(function () {
    // Public routes
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        
    });
});
