<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');

// Child Overview
Route::get('/child/{student}', [\App\Http\Controllers\Parent\ChildController::class, 'show'])->name('child.show');

// Attendance
Route::get('/attendance', [\App\Http\Controllers\Parent\AttendanceController::class, 'index'])->name('attendance.index');

// Assignments
Route::get('/assignments', [\App\Http\Controllers\Parent\AssignmentsController::class, 'index'])->name('assignments.index');

// Results
Route::get('/results', [\App\Http\Controllers\Parent\ResultController::class, 'index'])->name('results.index');

// Notices
Route::get('/notices', [\App\Http\Controllers\Parent\NoticeController::class, 'index'])->name('notices.index');
Route::get('/notices/{notice}', [\App\Http\Controllers\Parent\NoticeController::class, 'show'])->name('notices.show');

// News & Events
Route::get('/news-events', [\App\Http\Controllers\Parent\NewsEventsController::class, 'index'])->name('news-events.index');
Route::get('/news-events/{notice}', [\App\Http\Controllers\Parent\NewsEventsController::class, 'show'])->name('news-events.show');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Parent\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Http\Controllers\Parent\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Http\Controllers\Parent\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/preferences', [\App\Http\Controllers\Parent\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Http\Controllers\Parent\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Http\Controllers\Parent\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Http\Controllers\Parent\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Http\Controllers\Parent\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
