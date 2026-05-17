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
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\WebControlController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\ExecutiveController;
use App\Http\Controllers\Admin\HodController;
// Application feature removed
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\IdCardController;

// ── Dashboard ──────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ── User Management ────────────────────────────────────────
Route::resource('users', UserController::class);
Route::resource('executives', ExecutiveController::class);
Route::resource('hods', HodController::class);

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

Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('attendance/sessions/{attendanceSession}', [AttendanceController::class, 'session'])->name('attendance.sessions.show');

Route::resource('departments', DepartmentController::class);
Route::resource('programs', ProgramController::class);
Route::post('programs/bulk-action', [ProgramController::class, 'bulkAction'])->name('programs.bulk-action');

// ── People Management ──────────────────────────────────────
Route::resource('students', StudentController::class);
Route::post('students/bulk-promote', [StudentController::class, 'bulkPromote'])->name('students.bulk-promote');
Route::get('students/{student}/drawer', [StudentController::class, 'drawer'])->name('students.drawer');
Route::resource('teachers', TeacherController::class);
Route::get('teachers/{teacher}/drawer', [TeacherController::class, 'drawer'])->name('teachers.drawer');
Route::post('teachers/bulk-action', [TeacherController::class, 'bulkAction'])->name('teachers.bulk-action');
Route::resource('parents', ParentController::class);
Route::resource('alumni', AlumniController::class);
Route::post('alumni/{alumnus}/toggle-featured', [AlumniController::class, 'toggleFeatured'])->name('alumni.toggle-featured');
Route::post('staff/import', [StaffController::class, 'import'])->name('staff.import');
Route::get('staff/export/csv', [StaffController::class, 'exportCsv'])->name('staff.export.csv');
Route::get('staff/export/pdf', [StaffController::class, 'exportPdf'])->name('staff.export.pdf');
Route::resource('staff', StaffController::class);
Route::patch('staff/{staff}/status', [StaffController::class, 'updateStatus'])->name('staff.status.update');
Route::post('staff/{staff}/toggle-featured', [StaffController::class, 'toggleFeatured'])->name('staff.toggle-featured');
Route::post('staff/{staff}/toggle-public', [StaffController::class, 'togglePublic'])->name('staff.toggle-public');
Route::get('staff/{staff}/documents', [StaffController::class, 'documents'])->name('staff.documents');
Route::post('staff/{staff}/documents', [StaffController::class, 'storeDocument'])->name('staff.documents.store');
Route::delete('staff/{staff}/documents/{document}', [StaffController::class, 'destroyDocument'])->name('staff.documents.destroy');

// ── ID Cards ───────────────────────────────────────────────
Route::prefix('id-cards')->name('id-cards.')->group(function () {
    Route::get('students',                             [IdCardController::class, 'studentIndex'])->name('students.index');
    Route::get('students/{student}/pdf',               [IdCardController::class, 'studentSinglePdf'])->name('students.single-pdf');
    Route::post('students/bulk-pdf',                   [IdCardController::class, 'studentBulkPdf'])->name('students.bulk-pdf');
    Route::get('staff',                                [IdCardController::class, 'staffIndex'])->name('staff.index');
    Route::get('staff/{staff}/pdf',                    [IdCardController::class, 'staffSinglePdf'])->name('staff.single-pdf');
    Route::post('staff/bulk-pdf',                      [IdCardController::class, 'staffBulkPdf'])->name('staff.bulk-pdf');
});

// ── Examinations & Results ─────────────────────────────────
Route::get('exams/analytics', [ExamController::class, 'analytics'])->name('exams.analytics');
Route::get('exams/export/{format}', [ExamController::class, 'export'])->name('exams.export');
Route::get('exams/{exam}/marks/export/{format}', [ExamController::class, 'exportSubjectMarks'])->name('exams.marks.export');
Route::patch('exams/{exam}/subjects/{subject}/marking-scheme', [ExamController::class, 'updateSubjectMarkingScheme'])->name('exams.subjects.marking-scheme.update');
Route::resource('exams', ExamController::class);
Route::get('exams/{exam}/marks/{mark}/edit', [ExamController::class, 'editMark'])->name('exams.marks.edit');
Route::put('exams/{exam}/marks/{mark}', [ExamController::class, 'updateMark'])->name('exams.marks.update');
Route::get('exams/{exam}/students/{student}/sheet', [ExamController::class, 'resultSheet'])->name('exams.result-sheet');
Route::patch('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');

// ── Content & Communications ───────────────────────────────
Route::prefix('news-events')->name('news-events.')->group(function () {
    Route::get('/', [NoticeController::class, 'newsEventsIndex'])->name('index');
    Route::get('/create', [NoticeController::class, 'createNewsEvent'])->name('create');
    Route::post('/', [NoticeController::class, 'storeNewsEvent'])->name('store');
    Route::get('/{notice}', [NoticeController::class, 'showNewsEvent'])->name('show');
    Route::get('/{notice}/edit', [NoticeController::class, 'editNewsEvent'])->name('edit');
    Route::put('/{notice}', [NoticeController::class, 'updateNewsEvent'])->name('update');
    Route::delete('/{notice}', [NoticeController::class, 'destroyNewsEvent'])->name('destroy');
});
Route::resource('notices', NoticeController::class);
Route::resource('facilities', FacilityController::class);
Route::resource('executives', ExecutiveController::class);
Route::resource('media', MediaController::class);
Route::resource('downloads', DownloadController::class);
Route::get('downloads/{download}/file', [DownloadController::class, 'file'])->name('downloads.file');
Route::resource('banners', BannerController::class);
Route::get('roles-permissions', [RolePermissionController::class, 'index'])->name('roles-permissions.index');

// ── Resources (alias for Downloads with resource category) ─
Route::get('resources', [DownloadController::class, 'resources'])->name('resources.index');

// ── Web Control / Settings ─────────────────────────────────
Route::get('web-control', [WebControlController::class, 'index'])->name('web-control.index');
Route::post('web-control', [WebControlController::class, 'update'])->name('web-control.update');
Route::delete('web-control/file/{key}', [WebControlController::class, 'clearFile'])->name('web-control.clear-file');

// Application feature removed

// ── Security & Audit ───────────────────────────────────────
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

// ── Admin Settings (Personal Account) ─────────────────────
Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
Route::patch('settings/profile', [\App\Http\Controllers\Admin\SettingsController::class, 'updateProfile'])->name('settings.profile.update');
Route::patch('settings/password', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePassword'])->name('settings.password.update');
Route::patch('settings/two-factor', [\App\Http\Controllers\Admin\SettingsController::class, 'updateTwoFactor'])->name('settings.two-factor.update');
Route::patch('settings/preferences', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePreferences'])->name('settings.preferences.update');
Route::patch('settings/notifications', [\App\Http\Controllers\Admin\SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
Route::post('settings/logout-all', [\App\Http\Controllers\Admin\SettingsController::class, 'logoutAllDevices'])->name('settings.logout-all');
Route::post('settings/reset-dashboard', [\App\Http\Controllers\Admin\SettingsController::class, 'resetDashboard'])->name('settings.reset-dashboard');
Route::post('settings/clear-preferences', [\App\Http\Controllers\Admin\SettingsController::class, 'clearPreferences'])->name('settings.clear-preferences');
