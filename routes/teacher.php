<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

// My Classes
Route::resource('classes', \App\Http\Controllers\Teacher\ClassesController::class)->only(['index', 'show']);

// Attendance Entry
Route::resource('attendance', \App\Http\Controllers\Teacher\AttendanceController::class);
Route::get('attendance/load-students/{subject}', [\App\Http\Controllers\Teacher\AttendanceController::class, 'loadStudents'])->name('attendance.load-students');

// Student List
Route::resource('students', \App\Http\Controllers\Teacher\StudentsController::class)->only(['index', 'show']);

// Timetable
Route::resource('timetable', \App\Http\Controllers\Teacher\TimetableController::class)->only(['index', 'show']);

// Exams & Marks
Route::resource('exams', \App\Http\Controllers\Teacher\ExamsController::class)->only(['index']);
Route::get('exams/fill-marks', [\App\Http\Controllers\Teacher\ExamsController::class, 'fillMarks'])->name('exams.fill-marks');
Route::post('exams/save-marks', [\App\Http\Controllers\Teacher\ExamsController::class, 'saveMarks'])->name('exams.save-marks');

// Assignments
Route::resource('assignments', \App\Http\Controllers\Teacher\AssignmentsController::class);
Route::post('assignments/{assignment}/submissions/{submission}/grade', [\App\Http\Controllers\Teacher\AssignmentsController::class, 'gradeSubmission'])->name('assignments.submissions.grade');

// Downloads/Resources
Route::resource('downloads', \App\Http\Controllers\Teacher\DownloadController::class);
Route::get('downloads/{download}/file', [\App\Http\Controllers\Teacher\DownloadController::class, 'file'])->name('downloads.file');

// Notices
Route::resource('notices', \App\Http\Controllers\Teacher\NoticesController::class)->only(['index', 'show']);
Route::get('news-events', [\App\Http\Controllers\Teacher\NoticesController::class, 'newsEvents'])->name('news-events.index');
Route::get('news-events/{notice}', [\App\Http\Controllers\Teacher\NoticesController::class, 'showNewsEvent'])->name('news-events.show');

// Profile
Route::get('profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'show'])->name('profile.show');
Route::get('profile/edit', [\App\Http\Controllers\Teacher\ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'update'])->name('profile.update');
Route::get('profile/change-password', [\App\Http\Controllers\Teacher\ProfileController::class, 'changePassword'])->name('profile.change-password');
Route::post('profile/change-password', [\App\Http\Controllers\Teacher\ProfileController::class, 'updatePassword'])->name('profile.update-password');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Teacher\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Http\Controllers\Teacher\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Http\Controllers\Teacher\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/two-factor', [\App\Http\Controllers\Teacher\SettingsController::class, 'updateTwoFactor'])->name('two-factor.update');
    Route::patch('/preferences', [\App\Http\Controllers\Teacher\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Http\Controllers\Teacher\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Http\Controllers\Teacher\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Http\Controllers\Teacher\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Http\Controllers\Teacher\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
