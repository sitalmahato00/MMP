<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Modules\Student\Controllers\Web\DashboardController::class, 'index'])->name('dashboard');

// My Attendance
Route::resource('attendance', \App\Modules\Student\Controllers\Web\AttendanceController::class)->only(['index', 'show']);

// My Marks / Results
Route::resource('marks', \App\Modules\Student\Controllers\Web\MarksController::class)->only(['index', 'show']);

// My Subjects
Route::get('subjects', [\App\Modules\Student\Controllers\Web\SubjectsController::class, 'index'])->name('subjects.index');

// My Assignments
Route::resource('assignments', \App\Modules\Student\Controllers\Web\AssignmentsController::class)->only(['index', 'show']);
Route::post('assignments/{assignment}/submit', [\App\Modules\Student\Controllers\Web\AssignmentsController::class, 'submit'])->name('assignments.submit');

// My Timetable
Route::resource('timetable', \App\Modules\Student\Controllers\Web\TimetableController::class)->only(['index']);

// Downloads/Resources
Route::resource('downloads', \App\Modules\Student\Controllers\Web\DownloadController::class)->only(['index', 'show']);
Route::get('downloads/{download}/file', [\App\Modules\Student\Controllers\Web\DownloadController::class, 'file'])->name('downloads.file');

// Notices
Route::resource('notices', \App\Modules\Student\Controllers\Web\NoticesController::class)->only(['index', 'show']);
Route::get('news-events', [\App\Modules\Student\Controllers\Web\NoticesController::class, 'newsEvents'])->name('news-events.index');
Route::get('news-events/{notice}', [\App\Modules\Student\Controllers\Web\NoticesController::class, 'showNewsEvent'])->name('news-events.show');

// Profile
Route::get('profile', [\App\Modules\Student\Controllers\Web\ProfileController::class, 'show'])->name('profile.show');
Route::get('profile/edit', [\App\Modules\Student\Controllers\Web\ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [\App\Modules\Student\Controllers\Web\ProfileController::class, 'update'])->name('profile.update');
Route::get('profile/change-password', [\App\Modules\Student\Controllers\Web\ProfileController::class, 'changePassword'])->name('profile.change-password');
Route::post('profile/change-password', [\App\Modules\Student\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.update-password');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/two-factor', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'updateTwoFactor'])->name('two-factor.update');
    Route::patch('/preferences', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Modules\Student\Controllers\Web\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
