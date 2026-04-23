<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');

// My Attendance
Route::resource('attendance', \App\Http\Controllers\Student\AttendanceController::class)->only(['index', 'show']);

// My Marks / Results
Route::resource('marks', \App\Http\Controllers\Student\MarksController::class)->only(['index', 'show']);

// My Assignments
Route::resource('assignments', \App\Http\Controllers\Student\AssignmentsController::class)->only(['index', 'show']);
Route::post('assignments/{assignment}/submit', [\App\Http\Controllers\Student\AssignmentsController::class, 'submit'])->name('assignments.submit');

// My Timetable
Route::resource('timetable', \App\Http\Controllers\Student\TimetableController::class)->only(['index']);

// Downloads/Resources
Route::resource('downloads', \App\Http\Controllers\Student\DownloadController::class)->only(['index', 'show']);
Route::get('downloads/{download}/file', [\App\Http\Controllers\Student\DownloadController::class, 'file'])->name('downloads.file');

// Notices
Route::resource('notices', \App\Http\Controllers\Student\NoticesController::class)->only(['index', 'show']);

// Profile
Route::get('profile', [\App\Http\Controllers\Student\ProfileController::class, 'show'])->name('profile.show');
Route::get('profile/edit', [\App\Http\Controllers\Student\ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [\App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');
Route::get('profile/change-password', [\App\Http\Controllers\Student\ProfileController::class, 'changePassword'])->name('profile.change-password');
Route::post('profile/change-password', [\App\Http\Controllers\Student\ProfileController::class, 'updatePassword'])->name('profile.update-password');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Student\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Http\Controllers\Student\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Http\Controllers\Student\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/preferences', [\App\Http\Controllers\Student\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Http\Controllers\Student\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Http\Controllers\Student\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Http\Controllers\Student\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Http\Controllers\Student\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
