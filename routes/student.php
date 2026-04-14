<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');

// My Profile
// Attendance
// Marks / Results
// Timetable
// Assignments
// Downloads
// Notices
// Exams
// Performance
