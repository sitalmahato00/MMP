<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\HomeController;

// ─── Public Routes (SEO-optimized) ────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/notices', [HomeController::class, 'notices'])->name('public.notices');
Route::get('/news-events', [HomeController::class, 'newsEvents'])->name('public.news-events');
Route::get('/departments', [HomeController::class, 'departments'])->name('public.departments');
Route::get('/departments/{slug}', [HomeController::class, 'departmentShow'])->name('public.department.show');
Route::get('/downloads', [HomeController::class, 'downloads'])->name('public.downloads');
Route::get('/question-bank', [HomeController::class, 'questionBank'])->name('public.question-bank');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('public.gallery');
Route::get('/staff', [HomeController::class, 'staff'])->name('public.staff');
Route::get('/leadership', [HomeController::class, 'leadership'])->name('public.leadership');
Route::get('/facilities', [HomeController::class, 'facilities'])->name('public.facilities');
Route::get('/contact', [HomeController::class, 'contact'])->name('public.contact');
Route::get('/alumni', [HomeController::class, 'alumniDirectory'])->name('public.alumni');
Route::get('/alumni/{id}', [HomeController::class, 'alumniProfile'])->name('public.alumni.profile')->whereNumber('id');
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
