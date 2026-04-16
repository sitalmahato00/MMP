<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\WebControlController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\ExecutiveController;

// ── Dashboard ──────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ── User Management ────────────────────────────────────────
Route::resource('users', UserController::class);

// ── Academic Structure ─────────────────────────────────────
Route::resource('academic-sessions', AcademicSessionController::class);
Route::patch('academic-sessions/{academicSession}/set-current', [AcademicSessionController::class, 'setCurrent'])
    ->name('academic-sessions.set-current');

Route::resource('departments', DepartmentController::class);
Route::resource('programs', ProgramController::class);

// ── People Management ──────────────────────────────────────
Route::resource('students', StudentController::class);
Route::resource('teachers', TeacherController::class);
Route::resource('parents', ParentController::class);
Route::resource('alumni', AlumniController::class);
Route::resource('staff', StaffController::class);

// ── Examinations & Results ─────────────────────────────────
Route::resource('exams', ExamController::class);
Route::patch('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');

// ── Content & Communications ───────────────────────────────
Route::resource('notices', NoticeController::class);
Route::resource('facilities', FacilityController::class);
Route::resource('executives', ExecutiveController::class);
Route::resource('media', MediaController::class);
Route::resource('downloads', DownloadController::class);
Route::resource('banners', BannerController::class);

// ── Resources (alias for Downloads with resource category) ─
Route::get('resources', [DownloadController::class, 'resources'])->name('resources.index');

// ── Web Control / Settings ─────────────────────────────────
Route::get('web-control', [WebControlController::class, 'index'])->name('web-control.index');
Route::post('web-control', [WebControlController::class, 'update'])->name('web-control.update');

// ── Security & Audit ───────────────────────────────────────
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
