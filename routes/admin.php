<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

// Department Management
// User Management
// Academic Management (Exams + Results)
// Attendance Management
// Notice Management
// Website Content Management
// Reports
// Audit Logs
// Session Management
// Alumni Management
// Student Management
// Teacher Management
// HOD Management
// Parent Management
