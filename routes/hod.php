<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\HOD\DashboardController::class, 'index'])->name('dashboard');

// Student Management (department only)
Route::resource('students', \App\Http\Controllers\HOD\StudentController::class);
Route::get('students/{student}/drawer', [\App\Http\Controllers\HOD\StudentController::class, 'drawer'])->name('students.drawer');

// Teacher Management (department only)
Route::resource('teachers', \App\Http\Controllers\HOD\TeacherController::class);
Route::get('teachers/{teacher}/drawer', [\App\Http\Controllers\HOD\TeacherController::class, 'drawer'])->name('teachers.drawer');
// Attendance Management
// Exam Management
// Marks Management
// Timetable Management
// Notice Management
// Department Content
// Media Management
// Reports
// Alumni Preparation
