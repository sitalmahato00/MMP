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
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\AttendanceController::class, 'index'])->name('index');
    Route::get('/sessions', [\App\Http\Controllers\HOD\AttendanceController::class, 'sessions'])->name('sessions');
    Route::get('/reports', [\App\Http\Controllers\HOD\AttendanceController::class, 'reports'])->name('reports');
});

// Exam & Marks Management
Route::prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\ExamController::class, 'index'])->name('index');
    Route::get('/marks', [\App\Http\Controllers\HOD\ExamController::class, 'marks'])->name('marks');
    Route::get('/results', [\App\Http\Controllers\HOD\ExamController::class, 'results'])->name('results');
    Route::get('/analytics', [\App\Http\Controllers\HOD\ExamController::class, 'analytics'])->name('analytics');
});

// Timetable Management
Route::prefix('timetable')->name('timetable.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\TimetableController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\TimetableController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\TimetableController::class, 'store'])->name('store');
    Route::get('/{timetable}/edit', [\App\Http\Controllers\HOD\TimetableController::class, 'edit'])->name('edit');
    Route::put('/{timetable}', [\App\Http\Controllers\HOD\TimetableController::class, 'update'])->name('update');
});

// Notice Management
Route::prefix('notices')->name('notices.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\NoticeController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\NoticeController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\NoticeController::class, 'store'])->name('store');
    Route::get('/{notice}', [\App\Http\Controllers\HOD\NoticeController::class, 'show'])->name('show');
    Route::get('/{notice}/edit', [\App\Http\Controllers\HOD\NoticeController::class, 'edit'])->name('edit');
    Route::put('/{notice}', [\App\Http\Controllers\HOD\NoticeController::class, 'update'])->name('update');
    Route::delete('/{notice}', [\App\Http\Controllers\HOD\NoticeController::class, 'destroy'])->name('destroy');
});

// Department Content Management
Route::prefix('content')->name('content.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\ContentController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\ContentController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\ContentController::class, 'store'])->name('store');
    Route::get('/{content}', [\App\Http\Controllers\HOD\ContentController::class, 'show'])->name('show');
    Route::get('/{content}/edit', [\App\Http\Controllers\HOD\ContentController::class, 'edit'])->name('edit');
    Route::put('/{content}', [\App\Http\Controllers\HOD\ContentController::class, 'update'])->name('update');
    Route::delete('/{content}', [\App\Http\Controllers\HOD\ContentController::class, 'destroy'])->name('destroy');
});

// Media Management
Route::prefix('media')->name('media.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\MediaController::class, 'index'])->name('index');
    Route::post('/upload', [\App\Http\Controllers\HOD\MediaController::class, 'upload'])->name('upload');
    Route::delete('/{media}', [\App\Http\Controllers\HOD\MediaController::class, 'destroy'])->name('destroy');
    Route::get('/gallery', [\App\Http\Controllers\HOD\MediaController::class, 'gallery'])->name('gallery');
});

// Reports & Analytics
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\ReportController::class, 'index'])->name('index');
    Route::get('/attendance', [\App\Http\Controllers\HOD\ReportController::class, 'attendance'])->name('attendance');
    Route::get('/performance', [\App\Http\Controllers\HOD\ReportController::class, 'performance'])->name('performance');
    Route::get('/department', [\App\Http\Controllers\HOD\ReportController::class, 'department'])->name('department');
    Route::get('/export/{type}', [\App\Http\Controllers\HOD\ReportController::class, 'export'])->name('export');
});

// Alumni Preparation
Route::prefix('alumni')->name('alumni.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\AlumniController::class, 'index'])->name('index');
    Route::get('/graduating', [\App\Http\Controllers\HOD\AlumniController::class, 'graduating'])->name('graduating');
    Route::post('/prepare/{student}', [\App\Http\Controllers\HOD\AlumniController::class, 'prepare'])->name('prepare');
    Route::get('/records', [\App\Http\Controllers\HOD\AlumniController::class, 'records'])->name('records');
});
