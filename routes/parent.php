<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');

// Child Profile
// Attendance Monitoring
// Marks / Results
// Timetable
// Exams
// Notices
// Communication
// Performance Analytics
// Profile
