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
