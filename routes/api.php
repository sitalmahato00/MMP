<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PublicApiController;

// ─── API v1 Routes (JWT/Sanctum authenticated) ───────────
Route::prefix('v1')->group(function () {

    // Public API — Strict Gateway for external pages
    Route::prefix('public')->group(function () {
        Route::get('/homepage', [PublicApiController::class, 'homepage']);
        Route::get('/notices', [PublicApiController::class, 'notices']);
        Route::get('/departments', [PublicApiController::class, 'departments']);
        Route::get('/departments/{slug}', [PublicApiController::class, 'departmentShow']);
        Route::get('/alumni', [PublicApiController::class, 'alumni']);
        Route::get('/downloads', [PublicApiController::class, 'downloads']);
        Route::get('/pages/{slug}', [PublicApiController::class, 'page']);
    });

    // Authenticated API (future mobile/PWA use)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user()->load('student', 'teacher', 'parentProfile', 'alumnus');
        });
    });
});
