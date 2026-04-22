<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

// My Classes
Route::resource('classes', \App\Http\Controllers\Teacher\ClassesController::class)->only(['index', 'show']);

// Attendance Entry
Route::resource('attendance', \App\Http\Controllers\Teacher\AttendanceController::class);

// Marks Entry
Route::resource('marks', \App\Http\Controllers\Teacher\MarksController::class)->only(['index', 'show']);
Route::get('marks/{exam}/{subject}/fill', [\App\Http\Controllers\Teacher\MarksController::class, 'fillMarks'])->name('marks.fill');
Route::post('marks/{exam}/{subject}/save', [\App\Http\Controllers\Teacher\MarksController::class, 'saveMarks'])->name('marks.save');

// Student List
Route::resource('students', \App\Http\Controllers\Teacher\StudentsController::class)->only(['index', 'show']);

// Timetable
Route::resource('timetable', \App\Http\Controllers\Teacher\TimetableController::class)->only(['index', 'show']);

// Exams
Route::resource('exams', \App\Http\Controllers\Teacher\ExamsController::class)->only(['index', 'show']);

// Assignments
Route::resource('assignments', \App\Http\Controllers\Teacher\AssignmentsController::class);

// Notices
Route::resource('notices', \App\Http\Controllers\Teacher\NoticesController::class)->only(['index', 'show']);

// Profile
Route::get('profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'show'])->name('profile.show');
Route::get('profile/edit', [\App\Http\Controllers\Teacher\ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'update'])->name('profile.update');
Route::get('profile/change-password', [\App\Http\Controllers\Teacher\ProfileController::class, 'changePassword'])->name('profile.change-password');
Route::post('profile/change-password', [\App\Http\Controllers\Teacher\ProfileController::class, 'updatePassword'])->name('profile.update-password');
