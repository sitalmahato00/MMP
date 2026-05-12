<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Modules\Teacher\Controllers\Web\DashboardController::class, 'index'])->name('dashboard');

// My Classes
Route::resource('classes', \App\Modules\Teacher\Controllers\Web\ClassesController::class)->only(['index', 'show']);

// Attendance Entry
Route::resource('attendance', \App\Modules\Teacher\Controllers\Web\AttendanceController::class);
Route::get('attendance/load-students/{subject}', [\App\Modules\Teacher\Controllers\Web\AttendanceController::class, 'loadStudents'])->name('attendance.load-students');

// Student List
Route::resource('students', \App\Modules\Teacher\Controllers\Web\StudentsController::class)->only(['index', 'show']);

// Timetable
Route::resource('timetable', \App\Modules\Teacher\Controllers\Web\TimetableController::class)->only(['index', 'show']);

// Exams & Marks
Route::resource('exams', \App\Modules\Teacher\Controllers\Web\ExamsController::class)->only(['index']);
Route::get('exams/fill-marks', [\App\Modules\Teacher\Controllers\Web\ExamsController::class, 'fillMarks'])->name('exams.fill-marks');
Route::post('exams/save-marks', [\App\Modules\Teacher\Controllers\Web\ExamsController::class, 'saveMarks'])->name('exams.save-marks');

// Assignments
Route::resource('assignments', \App\Modules\Teacher\Controllers\Web\AssignmentsController::class);

// Downloads/Resources
Route::resource('downloads', \App\Modules\Teacher\Controllers\Web\DownloadController::class);
Route::get('downloads/{download}/file', [\App\Modules\Teacher\Controllers\Web\DownloadController::class, 'file'])->name('downloads.file');

// Notices
Route::resource('notices', \App\Modules\Teacher\Controllers\Web\NoticesController::class)->only(['index', 'show']);
Route::get('news-events', [\App\Modules\Teacher\Controllers\Web\NoticesController::class, 'newsEvents'])->name('news-events.index');
Route::get('news-events/{notice}', [\App\Modules\Teacher\Controllers\Web\NoticesController::class, 'showNewsEvent'])->name('news-events.show');

// Profile
Route::get('profile', [\App\Modules\Teacher\Controllers\Web\ProfileController::class, 'show'])->name('profile.show');
Route::get('profile/edit', [\App\Modules\Teacher\Controllers\Web\ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [\App\Modules\Teacher\Controllers\Web\ProfileController::class, 'update'])->name('profile.update');
Route::get('profile/change-password', [\App\Modules\Teacher\Controllers\Web\ProfileController::class, 'changePassword'])->name('profile.change-password');
Route::post('profile/change-password', [\App\Modules\Teacher\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.update-password');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/two-factor', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'updateTwoFactor'])->name('two-factor.update');
    Route::patch('/preferences', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Modules\Teacher\Controllers\Web\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
