<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\HomeController;

// ─── Public Routes (SEO-optimized) ────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/notices', [HomeController::class, 'notices'])->name('public.notices');
Route::get('/departments', [HomeController::class, 'departments'])->name('public.departments');
Route::get('/departments/{slug}', [HomeController::class, 'departmentShow'])->name('public.department.show');
Route::get('/downloads', [HomeController::class, 'downloads'])->name('public.downloads');
Route::get('/page/{slug}', [HomeController::class, 'page'])->name('public.page');

// ─── Auth Routes ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboardRedirect'])->name('dashboard');
});
