<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');

// Child Overview
Route::get('/child/{student}', [\App\Http\Controllers\Parent\ChildController::class, 'show'])->name('child.show');

// Attendance
Route::get('/attendance', [\App\Http\Controllers\Parent\AttendanceController::class, 'index'])->name('attendance.index');

// Results
Route::get('/results', [\App\Http\Controllers\Parent\ResultController::class, 'index'])->name('results.index');

// Notices
Route::get('/notices', [\App\Http\Controllers\Parent\NoticeController::class, 'index'])->name('notices.index');
