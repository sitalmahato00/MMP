<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\HOD\DashboardController::class, 'index'])->name('dashboard');

// Student Management (department only)
// Teacher Management (department only)
// Attendance Management
// Exam Management
// Marks Management
// Timetable Management
// Notice Management
// Department Content
// Media Management
// Reports
// Alumni Preparation
