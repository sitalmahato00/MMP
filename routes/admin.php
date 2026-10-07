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
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\ReportController;

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

// ── Attendance Operations ──────────────────────────────────
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/sessions', [AttendanceController::class, 'sessions'])->name('sessions');
    Route::get('/mark', [AttendanceController::class, 'mark'])->name('mark');
    Route::post('/load-students', [AttendanceController::class, 'loadStudents'])->name('load-students');
    Route::post('/store', [AttendanceController::class, 'store'])->name('store');
    Route::get('/reports', [AttendanceController::class, 'reports'])->name('reports');
    Route::get('/sessions/{attendanceSession}', [AttendanceController::class, 'session'])->name('sessions.show');
    Route::get('/sessions/{attendanceSession}/edit', [AttendanceController::class, 'edit'])->name('sessions.edit');
    Route::put('/sessions/{attendanceSession}', [AttendanceController::class, 'update'])->name('sessions.update');
    Route::delete('/sessions/{attendanceSession}', [AttendanceController::class, 'destroy'])->name('sessions.destroy');
    Route::patch('/records/{attendance}/toggle', [AttendanceController::class, 'toggleStatus'])->name('records.toggle');
});

Route::resource('departments', DepartmentController::class);
Route::resource('programs', ProgramController::class);
Route::post('programs/bulk-action', [ProgramController::class, 'bulkAction'])->name('programs.bulk-action');

// ── Academic Subjects ──────────────────────────────────────
Route::resource('subjects', SubjectController::class);
Route::get('subjects/{subject}/drawer', [SubjectController::class, 'drawer'])->name('subjects.drawer');
Route::post('subjects/{subject}/assign-teacher', [SubjectController::class, 'assignTeacher'])->name('subjects.assign-teacher');
Route::delete('subjects/{subject}/teachers/{teacher}', [SubjectController::class, 'removeTeacher'])->name('subjects.remove-teacher');

// ── Timetable Management ───────────────────────────────────
Route::resource('timetable', TimetableController::class);
Route::post('timetable/{timetable}/slots', [TimetableController::class, 'storeSlot'])->name('timetable.slots.store');
Route::delete('timetable/{timetable}/slots/{slot}', [TimetableController::class, 'destroySlot'])->name('timetable.slots.destroy');
Route::get('timetable/{timetable}/export', [TimetableController::class, 'export'])->name('timetable.export');
Route::post('timetable/{timetable}/check-teacher-conflicts', [TimetableController::class, 'checkTeacherConflicts'])->name('timetable.check-teacher-conflicts');

// ── Assignments Management ─────────────────────────────────
Route::resource('assignments', AssignmentController::class);
Route::post('assignments/{assignment}/submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])->name('assignments.submissions.grade');

// ── People Management ──────────────────────────────────────
Route::resource('students', StudentController::class);
    Route::get('students/{student}/json', [StudentController::class, 'json'])->name('students.json');
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
    Route::get('students/search',                      [IdCardController::class, 'studentSearch'])->name('students.search');
    Route::get('students/bulk-list',                   [IdCardController::class, 'studentBulkList'])->name('students.bulk-list');
    Route::get('students/reports',                     [IdCardController::class, 'reports'])->name('students.reports');
    Route::get('students/report-print', [IdCardController::class, 'reportPrint'])->name('students.report-print');
    Route::get('students/reports/export',              [IdCardController::class, 'reportExport'])->name('students.reports.export');
    Route::get('students',                             [IdCardController::class, 'studentIndex'])->name('students.index');
    Route::get('students/{student}/pdf',               [IdCardController::class, 'studentSinglePdf'])->name('students.single-pdf');
    Route::post('students/bulk-pdf',                   [IdCardController::class, 'studentBulkPdf'])->name('students.bulk-pdf');
});

// ── Examinations & Results ─────────────────────────────────
Route::get('exams/analytics', [ExamController::class, 'analytics'])->name('exams.analytics');
Route::get('exams/export/{format}', [ExamController::class, 'export'])->name('exams.export');
Route::get('exams/fill-marks', [ExamController::class, 'fillMarks'])->name('exams.fill-marks');
Route::post('exams/save-marks', [ExamController::class, 'saveMarks'])->name('exams.save-marks');
Route::post('exams/verify-marks', [ExamController::class, 'verifyMarks'])->name('exams.verify-marks');
Route::get('exams/{exam}/marks/export/{format}', [ExamController::class, 'exportSubjectMarks'])->name('exams.marks.export');
Route::get('exams/{exam}/subjects/{subject}/marks', [ExamController::class, 'showSubjectMarks'])->name('exams.subjects.marks');
Route::patch('exams/{exam}/subjects/{subject}/marking-scheme', [ExamController::class, 'updateSubjectMarkingScheme'])->name('exams.subjects.marking-scheme.update');
Route::resource('exams', ExamController::class);
Route::get('exams/{exam}/marks/{mark}/edit', [ExamController::class, 'editMark'])->name('exams.marks.edit');
Route::put('exams/{exam}/marks/{mark}', [ExamController::class, 'updateMark'])->name('exams.marks.update');
Route::delete('exams/{exam}/subjects/{subject}/marks', [ExamController::class, 'destroySubjectMarks'])->name('exams.marks.destroy');
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

Route::patch('notices/{notice}/toggle-popup', [NoticeController::class, 'togglePopup'])->name('notices.toggle-popup');
Route::post('notices/{notice}/approve-main-site', [NoticeController::class, 'approveMainSite'])->name('notices.approve-main-site');
Route::post('notices/{notice}/reject-main-site', [NoticeController::class, 'rejectMainSite'])->name('notices.reject-main-site');
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

// ── Central Reports Hub ───────────────────────────────────
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/export-csv', [ReportController::class, 'exportCsv'])->name('export.csv');
    Route::get('/export-print', [ReportController::class, 'exportPrint'])->name('export.print');
});

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
