<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Modules\Parent\Controllers\Web\DashboardController::class, 'index'])->name('dashboard');

// Child Overview
Route::get('/child/{student}', [\App\Modules\Parent\Controllers\Web\ChildController::class, 'show'])->name('child.show');

// Attendance
Route::get('/attendance', [\App\Modules\Parent\Controllers\Web\AttendanceController::class, 'index'])->name('attendance.index');

// Assignments
Route::get('/assignments', [\App\Modules\Parent\Controllers\Web\AssignmentsController::class, 'index'])->name('assignments.index');

// Results
Route::get('/results', [\App\Modules\Parent\Controllers\Web\ResultController::class, 'index'])->name('results.index');
Route::get('/results/{student}/{exam}', [\App\Modules\Parent\Controllers\Web\ResultController::class, 'show'])->name('results.show');

// Subjects
Route::get('/subjects', [\App\Modules\Parent\Controllers\Web\SubjectsController::class, 'index'])->name('subjects.index');

// Notices
Route::get('/notices', [\App\Modules\Parent\Controllers\Web\NoticeController::class, 'index'])->name('notices.index');
Route::get('/notices/{notice}', [\App\Modules\Parent\Controllers\Web\NoticeController::class, 'show'])->name('notices.show');

// News & Events
Route::get('/news-events', [\App\Modules\Parent\Controllers\Web\NewsEventsController::class, 'index'])->name('news-events.index');
Route::get('/news-events/{notice}', [\App\Modules\Parent\Controllers\Web\NewsEventsController::class, 'show'])->name('news-events.show');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/two-factor', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'updateTwoFactor'])->name('two-factor.update');
    Route::patch('/preferences', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Modules\Parent\Controllers\Web\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
