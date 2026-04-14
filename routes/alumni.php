<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Alumni\DashboardController::class, 'index'])->name('dashboard');

// Profile
// Notices
// Events
