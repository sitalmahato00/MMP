<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

// My Classes
// Attendance Entry
// Marks Entry
// Exam View
// Timetable
// Assignments
// Student List
// Performance View
// Notices
// Profile
