<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PublicApiController;
use App\Http\Controllers\Api\AuthController;

// ─── OTP Authentication Routes ───────────
Route::prefix('auth')->middleware('throttle:3,1')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout route (requires authentication)
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ─── API v1 Routes (JWT/Sanctum authenticated) ───────────
Route::prefix('v1')->group(function () {

    // Public API — Strict Gateway for external pages
    Route::prefix('public')->middleware('throttle:public-api')->group(function () {
        Route::get('/homepage', [PublicApiController::class, 'homepage']);
        Route::get('/notices', [PublicApiController::class, 'notices']);
        Route::get('/departments', [PublicApiController::class, 'departments']);
        Route::get('/departments/{slug}', [PublicApiController::class, 'departmentShow']);
        Route::get('/alumni', [PublicApiController::class, 'alumni']);
        Route::get('/downloads', [PublicApiController::class, 'downloads']);
        Route::get('/pages/{slug}', [PublicApiController::class, 'page']);
        Route::get('/facilities', [PublicApiController::class, 'facilities']);
        Route::get('/staff', [PublicApiController::class, 'staff']);
        Route::get('/leadership', [PublicApiController::class, 'leadership']);
        Route::get('/site-settings', [PublicApiController::class, 'siteSettings']);
    });

    // Authenticated API (future mobile/PWA use)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user()->load('student', 'teacher', 'parentProfile', 'alumnus');
        });
    });

    // Subject API (authenticated users only)
    Route::middleware('auth:sanctum')->get('/subjects/{subject}/students', [\App\Http\Controllers\Api\SubjectController::class, 'students']);
});
