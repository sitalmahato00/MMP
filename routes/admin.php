<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AnalyticsController;
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
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\WebControlController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\ExecutiveController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RolePermissionController;

// ── Dashboard ──────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export/{format}', [ReportController::class, 'export'])->name('reports.export');

// ── User Management ────────────────────────────────────────
Route::resource('users', UserController::class);

// ── Academic Structure ─────────────────────────────────────
Route::resource('academic-sessions', AcademicSessionController::class);
Route::patch('academic-sessions/{academicSession}/set-current', [AcademicSessionController::class, 'setCurrent'])
    ->name('academic-sessions.set-current');
Route::get('academic-sessions/{academicSession}/preview-end', [AcademicSessionController::class, 'previewEnd'])
    ->name('academic-sessions.preview-end');
Route::post('academic-sessions/{academicSession}/end', [AcademicSessionController::class, 'endSession'])
    ->name('academic-sessions.end');
Route::post('academic-sessions/{academicSession}/semesters', [AcademicSessionController::class, 'storeSemester'])
    ->name('academic-sessions.semesters.store');
Route::put('academic-sessions/{academicSession}/semesters/{semester}', [AcademicSessionController::class, 'updateSemester'])
    ->name('academic-sessions.semesters.update');
Route::delete('academic-sessions/{academicSession}/semesters/{semester}', [AcademicSessionController::class, 'destroySemester'])
    ->name('academic-sessions.semesters.destroy');
Route::get('academic-sessions/{academicSession}/preview-advance', [AcademicSessionController::class, 'previewAdvance'])
    ->name('academic-sessions.preview-advance');
Route::post('academic-sessions/{academicSession}/advance', [AcademicSessionController::class, 'advanceSemesters'])
    ->name('academic-sessions.advance');

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
Route::get('downloads/{download}/file', [DownloadController::class, 'file'])->name('downloads.file');
Route::resource('banners', BannerController::class);
Route::get('messages', [CommunicationController::class, 'index'])->name('messages.index');
Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions.index');

// ── Resources (alias for Downloads with resource category) ─
Route::get('resources', [DownloadController::class, 'resources'])->name('resources.index');

// ── Web Control / Settings ─────────────────────────────────
Route::get('web-control', [WebControlController::class, 'index'])->name('web-control.index');
Route::post('web-control', [WebControlController::class, 'update'])->name('web-control.update');
Route::delete('web-control/file/{key}', [WebControlController::class, 'clearFile'])->name('web-control.clear-file');

// ── Applications ───────────────────────────────────────────
Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
Route::patch('applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('applications.update-status');
Route::delete('applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');

// ── Security & Audit ───────────────────────────────────────
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
