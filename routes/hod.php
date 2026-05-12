<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Modules\HOD\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Student Management (department only)
Route::resource('students', \App\Modules\Student\Controllers\Admin\HodStudentController::class);
Route::get('students/{student}/drawer', [\App\Modules\Student\Controllers\Admin\HodStudentController::class, 'drawer'])->name('students.drawer');
Route::get('students-export', [\App\Modules\Student\Controllers\Admin\HodStudentController::class, 'export'])->name('students.export');

// Teacher Management (department only)
Route::resource('teachers', \App\Modules\Teacher\Controllers\Admin\HodTeacherController::class);
Route::get('teachers/{teacher}/drawer', [\App\Modules\Teacher\Controllers\Admin\HodTeacherController::class, 'drawer'])->name('teachers.drawer');

// Attendance Management
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\AttendanceController::class, 'index'])->name('index');
    Route::get('/mark', [\App\Modules\HOD\Controllers\AttendanceController::class, 'mark'])->name('mark');
    Route::post('/store', [\App\Modules\HOD\Controllers\AttendanceController::class, 'store'])->name('store');
    Route::get('/sessions', [\App\Modules\HOD\Controllers\AttendanceController::class, 'sessions'])->name('sessions');
    Route::get('/reports', [\App\Modules\HOD\Controllers\AttendanceController::class, 'reports'])->name('reports');
    Route::get('/{attendanceSession}/edit', [\App\Modules\HOD\Controllers\AttendanceController::class, 'edit'])->name('edit');
    Route::put('/{attendanceSession}', [\App\Modules\HOD\Controllers\AttendanceController::class, 'update'])->name('update');
    Route::delete('/{attendanceSession}', [\App\Modules\HOD\Controllers\AttendanceController::class, 'destroy'])->name('destroy');
});

// Exam & Marks Management
Route::prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\ExamController::class, 'index'])->name('index');
    Route::get('/create', [\App\Modules\HOD\Controllers\ExamController::class, 'create'])->name('create');
    Route::post('/', [\App\Modules\HOD\Controllers\ExamController::class, 'store'])->name('store');
    Route::get('/{exam}/edit', [\App\Modules\HOD\Controllers\ExamController::class, 'edit'])->name('edit');
    Route::put('/{exam}', [\App\Modules\HOD\Controllers\ExamController::class, 'update'])->name('update');
    Route::delete('/{exam}', [\App\Modules\HOD\Controllers\ExamController::class, 'destroy'])->name('destroy');
    Route::delete('/{exam}/force', [\App\Modules\HOD\Controllers\ExamController::class, 'forceDestroy'])->name('force-destroy');
    Route::get('/marks', [\App\Modules\HOD\Controllers\ExamController::class, 'marks'])->name('marks');
    Route::get('/fill-marks', [\App\Modules\HOD\Controllers\ExamController::class, 'fillMarks'])->name('fill-marks');
    Route::post('/save-marks', [\App\Modules\HOD\Controllers\ExamController::class, 'saveMarks'])->name('save-marks');
    Route::post('/verify-marks', [\App\Modules\HOD\Controllers\ExamController::class, 'verifyMarks'])->name('verify-marks');
    Route::get('/{exam}/edit-marking-scheme', [\App\Modules\HOD\Controllers\ExamController::class, 'editMarkingScheme'])->name('edit-marking-scheme');
    Route::put('/{exam}/update-marking-scheme', [\App\Modules\HOD\Controllers\ExamController::class, 'updateMarkingScheme'])->name('update-marking-scheme');
    Route::get('/export-marks', [\App\Modules\HOD\Controllers\ExamController::class, 'exportMarks'])->name('export-marks');
    Route::get('/results', [\App\Modules\HOD\Controllers\ExamController::class, 'results'])->name('results');
    Route::get('/analytics', [\App\Modules\HOD\Controllers\ExamController::class, 'analytics'])->name('analytics');
});

// Timetable Management
Route::prefix('timetable')->name('timetable.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\TimetableController::class, 'index'])->name('index');
    Route::get('/create', [\App\Modules\HOD\Controllers\TimetableController::class, 'create'])->name('create');
    Route::post('/', [\App\Modules\HOD\Controllers\TimetableController::class, 'store'])->name('store');
    Route::get('/{timetable}', [\App\Modules\HOD\Controllers\TimetableController::class, 'show'])->name('show');
    Route::get('/{timetable}/edit', [\App\Modules\HOD\Controllers\TimetableController::class, 'edit'])->name('edit');
    Route::put('/{timetable}', [\App\Modules\HOD\Controllers\TimetableController::class, 'update'])->name('update');
    Route::delete('/{timetable}', [\App\Modules\HOD\Controllers\TimetableController::class, 'destroy'])->name('destroy');
    Route::delete('/{timetable}/slots/{slot}', [\App\Modules\HOD\Controllers\TimetableController::class, 'destroySlot'])->name('slots.destroy');
    
    // Export functionality
    Route::get('/{timetable}/export', [\App\Modules\HOD\Controllers\TimetableController::class, 'export'])->name('export');
    
    // API endpoints for validations
    Route::post('/{timetable}/check-teacher-conflicts', [\App\Modules\HOD\Controllers\TimetableController::class, 'checkTeacherConflicts'])->name('check-teacher-conflicts');
    Route::get('/{timetable}/available-groups', [\App\Modules\HOD\Controllers\TimetableController::class, 'getAvailableGroups'])->name('available-groups');
    Route::get('/{timetable}/subject-teachers', [\App\Modules\HOD\Controllers\TimetableController::class, 'getSubjectTeachers'])->name('subject-teachers');
});

// Notice Management
Route::prefix('notices')->name('notices.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\NoticeController::class, 'index'])->name('index');
    Route::get('/create', [\App\Modules\HOD\Controllers\NoticeController::class, 'create'])->name('create');
    Route::post('/', [\App\Modules\HOD\Controllers\NoticeController::class, 'store'])->name('store');
    Route::get('/{notice}', [\App\Modules\HOD\Controllers\NoticeController::class, 'show'])->name('show');
    Route::get('/{notice}/edit', [\App\Modules\HOD\Controllers\NoticeController::class, 'edit'])->name('edit');
    Route::put('/{notice}', [\App\Modules\HOD\Controllers\NoticeController::class, 'update'])->name('update');
    Route::delete('/{notice}', [\App\Modules\HOD\Controllers\NoticeController::class, 'destroy'])->name('destroy');
});

Route::prefix('news-events')->name('news-events.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\NoticeController::class, 'newsEvents'])->name('index');
    Route::get('/create', [\App\Modules\HOD\Controllers\NoticeController::class, 'createNewsEvent'])->name('create');
    Route::post('/', [\App\Modules\HOD\Controllers\NoticeController::class, 'storeNewsEvent'])->name('store');
    Route::get('/{notice}', [\App\Modules\HOD\Controllers\NoticeController::class, 'showNewsEvent'])->name('show');
    Route::get('/{notice}/edit', [\App\Modules\HOD\Controllers\NoticeController::class, 'editNewsEvent'])->name('edit');
    Route::put('/{notice}', [\App\Modules\HOD\Controllers\NoticeController::class, 'updateNewsEvent'])->name('update');
    Route::delete('/{notice}', [\App\Modules\HOD\Controllers\NoticeController::class, 'destroyNewsEvent'])->name('destroy');
});

// Facilities & Resources Management
Route::prefix('facilities')->name('facilities.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\FacilityController::class, 'index'])->name('index');
    Route::get('/create', [\App\Modules\HOD\Controllers\FacilityController::class, 'create'])->name('create');
    Route::post('/', [\App\Modules\HOD\Controllers\FacilityController::class, 'store'])->name('store');
    Route::get('/{content}', [\App\Modules\HOD\Controllers\FacilityController::class, 'show'])->name('show');
    Route::get('/{content}/edit', [\App\Modules\HOD\Controllers\FacilityController::class, 'edit'])->name('edit');
    Route::put('/{content}', [\App\Modules\HOD\Controllers\FacilityController::class, 'update'])->name('update');
    Route::delete('/{content}', [\App\Modules\HOD\Controllers\FacilityController::class, 'destroy'])->name('destroy');
});

// Media Management
Route::prefix('media')->name('media.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\MediaController::class, 'index'])->name('index');
    Route::post('/upload', [\App\Modules\HOD\Controllers\MediaController::class, 'upload'])->name('upload');
    Route::delete('/{media}', [\App\Modules\HOD\Controllers\MediaController::class, 'destroy'])->name('destroy');
    Route::get('/gallery', [\App\Modules\HOD\Controllers\MediaController::class, 'gallery'])->name('gallery');
});

// Reports & Analytics
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\AttendanceController::class, 'reports'])->name('index');
    Route::get('/attendance', [\App\Modules\HOD\Controllers\AttendanceController::class, 'reports'])->name('attendance');
    Route::get('/performance', fn () => redirect()->route('hod.dashboard'));
    Route::get('/department', fn () => redirect()->route('hod.dashboard'));
    Route::get('/export/{type}', fn () => redirect()->route('hod.dashboard'));
});

// Alumni Preparation
Route::prefix('alumni')->name('alumni.')->group(function () {
    Route::get('/', [\App\Modules\Alumni\Controllers\Admin\HodAlumniController::class, 'index'])->name('index');
    Route::get('/graduating', [\App\Modules\Alumni\Controllers\Admin\HodAlumniController::class, 'graduating'])->name('graduating');
    Route::post('/prepare/{student}', [\App\Modules\Alumni\Controllers\Admin\HodAlumniController::class, 'prepare'])->name('prepare');
    Route::get('/records', [\App\Modules\Alumni\Controllers\Admin\HodAlumniController::class, 'records'])->name('records');
});

// Subject Management (department subjects only)
Route::prefix('subjects')->name('subjects.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\SubjectController::class, 'index'])->name('index');
    Route::get('/create', [\App\Modules\HOD\Controllers\SubjectController::class, 'create'])->name('create');
    Route::post('/', [\App\Modules\HOD\Controllers\SubjectController::class, 'store'])->name('store');
    Route::get('/{subject}', [\App\Modules\HOD\Controllers\SubjectController::class, 'show'])->name('show');
    Route::get('/{subject}/drawer', [\App\Modules\HOD\Controllers\SubjectController::class, 'drawer'])->name('drawer');
    Route::get('/{subject}/edit', [\App\Modules\HOD\Controllers\SubjectController::class, 'edit'])->name('edit');
    Route::put('/{subject}', [\App\Modules\HOD\Controllers\SubjectController::class, 'update'])->name('update');
    Route::delete('/{subject}', [\App\Modules\HOD\Controllers\SubjectController::class, 'destroy'])->name('destroy');
    Route::post('/{subject}/assign-teacher', [\App\Modules\HOD\Controllers\SubjectController::class, 'assignTeacher'])->name('assign-teacher');
    Route::delete('/{subject}/teachers/{teacher}', [\App\Modules\HOD\Controllers\SubjectController::class, 'removeTeacher'])->name('remove-teacher');
});

// Settings & Profile Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Modules\HOD\Controllers\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Modules\HOD\Controllers\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Modules\HOD\Controllers\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/two-factor', [\App\Modules\HOD\Controllers\SettingsController::class, 'updateTwoFactor'])->name('two-factor.update');
    Route::patch('/preferences', [\App\Modules\HOD\Controllers\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Modules\HOD\Controllers\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Modules\HOD\Controllers\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Modules\HOD\Controllers\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Modules\HOD\Controllers\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});

// HOD Resource Management (Downloads)
Route::resource('downloads', \App\Modules\HOD\Controllers\DownloadController::class)->except(['show']);
Route::get('downloads/{download}/file', [\App\Modules\HOD\Controllers\DownloadController::class, 'file'])->name('downloads.file');
