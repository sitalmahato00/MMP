<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\HOD\DashboardController::class, 'index'])->name('dashboard');

// Student Management (department only)
Route::resource('students', \App\Http\Controllers\HOD\StudentController::class);
Route::get('students/{student}/drawer', [\App\Http\Controllers\HOD\StudentController::class, 'drawer'])->name('students.drawer');
Route::get('students-export', [\App\Http\Controllers\HOD\StudentController::class, 'export'])->name('students.export');

// Teacher Management (department only)
Route::resource('teachers', \App\Http\Controllers\HOD\TeacherController::class);
Route::get('teachers/{teacher}/drawer', [\App\Http\Controllers\HOD\TeacherController::class, 'drawer'])->name('teachers.drawer');

// Attendance Management
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\AttendanceController::class, 'index'])->name('index');
    Route::get('/mark', [\App\Http\Controllers\HOD\AttendanceController::class, 'mark'])->name('mark');
    Route::post('/store', [\App\Http\Controllers\HOD\AttendanceController::class, 'store'])->name('store');
    Route::get('/sessions', [\App\Http\Controllers\HOD\AttendanceController::class, 'sessions'])->name('sessions');
    Route::get('/reports', [\App\Http\Controllers\HOD\AttendanceController::class, 'reports'])->name('reports');
    Route::get('/{attendanceSession}/edit', [\App\Http\Controllers\HOD\AttendanceController::class, 'edit'])->name('edit');
    Route::put('/{attendanceSession}', [\App\Http\Controllers\HOD\AttendanceController::class, 'update'])->name('update');
    Route::delete('/{attendanceSession}', [\App\Http\Controllers\HOD\AttendanceController::class, 'destroy'])->name('destroy');
});

// Exam & Marks Management
Route::prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\ExamController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\ExamController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\ExamController::class, 'store'])->name('store');
    Route::get('/{exam}/edit', [\App\Http\Controllers\HOD\ExamController::class, 'edit'])->name('edit');
    Route::put('/{exam}', [\App\Http\Controllers\HOD\ExamController::class, 'update'])->name('update');
    Route::delete('/{exam}', [\App\Http\Controllers\HOD\ExamController::class, 'destroy'])->name('destroy');
    Route::delete('/{exam}/force', [\App\Http\Controllers\HOD\ExamController::class, 'forceDestroy'])->name('force-destroy');
    Route::get('/marks', [\App\Http\Controllers\HOD\ExamController::class, 'marks'])->name('marks');
    Route::get('/{exam}/subjects/{subject}/marks', [\App\Http\Controllers\HOD\ExamController::class, 'showSubjectMarks'])->name('subjects.marks');
    Route::get('/fill-marks', [\App\Http\Controllers\HOD\ExamController::class, 'fillMarks'])->name('fill-marks');
    Route::post('/save-marks', [\App\Http\Controllers\HOD\ExamController::class, 'saveMarks'])->name('save-marks');
    Route::post('/verify-marks', [\App\Http\Controllers\HOD\ExamController::class, 'verifyMarks'])->name('verify-marks');
    Route::get('/{exam}/edit-marking-scheme', [\App\Http\Controllers\HOD\ExamController::class, 'editMarkingScheme'])->name('edit-marking-scheme');
    Route::put('/{exam}/update-marking-scheme', [\App\Http\Controllers\HOD\ExamController::class, 'updateMarkingScheme'])->name('update-marking-scheme');
    Route::get('/export-marks', [\App\Http\Controllers\HOD\ExamController::class, 'exportMarks'])->name('export-marks');
    Route::get('/results', [\App\Http\Controllers\HOD\ExamController::class, 'results'])->name('results');
    Route::get('/analytics', [\App\Http\Controllers\HOD\ExamController::class, 'analytics'])->name('analytics');
});

// Timetable Management
Route::prefix('timetable')->name('timetable.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\TimetableController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\TimetableController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\TimetableController::class, 'store'])->name('store');
    Route::get('/{timetable}', [\App\Http\Controllers\HOD\TimetableController::class, 'show'])->name('show');
    Route::get('/{timetable}/edit', [\App\Http\Controllers\HOD\TimetableController::class, 'edit'])->name('edit');
    Route::put('/{timetable}', [\App\Http\Controllers\HOD\TimetableController::class, 'update'])->name('update');
    Route::delete('/{timetable}', [\App\Http\Controllers\HOD\TimetableController::class, 'destroy'])->name('destroy');
    Route::delete('/{timetable}/slots/{slot}', [\App\Http\Controllers\HOD\TimetableController::class, 'destroySlot'])->name('slots.destroy');
    Route::post('/{timetable}/slots', [\App\Http\Controllers\HOD\TimetableController::class, 'storeSlot'])->name('slots.store');
    
    // Export functionality
    Route::get('/{timetable}/export', [\App\Http\Controllers\HOD\TimetableController::class, 'export'])->name('export');
    
    // API endpoints for validations
    Route::post('/{timetable}/check-teacher-conflicts', [\App\Http\Controllers\HOD\TimetableController::class, 'checkTeacherConflicts'])->name('check-teacher-conflicts');
    Route::get('/{timetable}/available-groups', [\App\Http\Controllers\HOD\TimetableController::class, 'getAvailableGroups'])->name('available-groups');
    Route::get('/{timetable}/subject-teachers', [\App\Http\Controllers\HOD\TimetableController::class, 'getSubjectTeachers'])->name('subject-teachers');
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

Route::prefix('news-events')->name('news-events.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\NoticeController::class, 'newsEvents'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\NoticeController::class, 'createNewsEvent'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\NoticeController::class, 'storeNewsEvent'])->name('store');
    Route::get('/{notice}', [\App\Http\Controllers\HOD\NoticeController::class, 'showNewsEvent'])->name('show');
    Route::get('/{notice}/edit', [\App\Http\Controllers\HOD\NoticeController::class, 'editNewsEvent'])->name('edit');
    Route::put('/{notice}', [\App\Http\Controllers\HOD\NoticeController::class, 'updateNewsEvent'])->name('update');
    Route::delete('/{notice}', [\App\Http\Controllers\HOD\NoticeController::class, 'destroyNewsEvent'])->name('destroy');
});

// Facilities & Resources Management
Route::prefix('facilities')->name('facilities.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\FacilityController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\FacilityController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\FacilityController::class, 'store'])->name('store');
    Route::get('/{content}', [\App\Http\Controllers\HOD\FacilityController::class, 'show'])->name('show');
    Route::get('/{content}/edit', [\App\Http\Controllers\HOD\FacilityController::class, 'edit'])->name('edit');
    Route::put('/{content}', [\App\Http\Controllers\HOD\FacilityController::class, 'update'])->name('update');
    Route::delete('/{content}', [\App\Http\Controllers\HOD\FacilityController::class, 'destroy'])->name('destroy');
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
    Route::get('/', [\App\Http\Controllers\HOD\AttendanceController::class, 'reports'])->name('index');
    Route::get('/attendance', [\App\Http\Controllers\HOD\AttendanceController::class, 'reports'])->name('attendance');
    Route::get('/performance', fn () => redirect()->route('hod.dashboard'));
    Route::get('/department', fn () => redirect()->route('hod.dashboard'));
    Route::get('/export/{type}', fn () => redirect()->route('hod.dashboard'));
});

// Alumni Preparation
Route::prefix('alumni')->name('alumni.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\AlumniController::class, 'index'])->name('index');
    Route::get('/graduating', [\App\Http\Controllers\HOD\AlumniController::class, 'graduating'])->name('graduating');
    Route::post('/prepare/{student}', [\App\Http\Controllers\HOD\AlumniController::class, 'prepare'])->name('prepare');
    Route::get('/records', [\App\Http\Controllers\HOD\AlumniController::class, 'records'])->name('records');
});

// Subject Management (department subjects only)
Route::prefix('subjects')->name('subjects.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\SubjectController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\HOD\SubjectController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\HOD\SubjectController::class, 'store'])->name('store');
    Route::get('/{subject}', [\App\Http\Controllers\HOD\SubjectController::class, 'show'])->name('show');
    Route::get('/{subject}/drawer', [\App\Http\Controllers\HOD\SubjectController::class, 'drawer'])->name('drawer');
    Route::get('/{subject}/edit', [\App\Http\Controllers\HOD\SubjectController::class, 'edit'])->name('edit');
    Route::put('/{subject}', [\App\Http\Controllers\HOD\SubjectController::class, 'update'])->name('update');
    Route::delete('/{subject}', [\App\Http\Controllers\HOD\SubjectController::class, 'destroy'])->name('destroy');
    Route::post('/{subject}/assign-teacher', [\App\Http\Controllers\HOD\SubjectController::class, 'assignTeacher'])->name('assign-teacher');
    Route::delete('/{subject}/teachers/{teacher}', [\App\Http\Controllers\HOD\SubjectController::class, 'removeTeacher'])->name('remove-teacher');
});

// Settings & Profile Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HOD\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Http\Controllers\HOD\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Http\Controllers\HOD\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/two-factor', [\App\Http\Controllers\HOD\SettingsController::class, 'updateTwoFactor'])->name('two-factor.update');
    Route::patch('/preferences', [\App\Http\Controllers\HOD\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Http\Controllers\HOD\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Http\Controllers\HOD\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Http\Controllers\HOD\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Http\Controllers\HOD\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});

// HOD Resource Management (Downloads)
Route::resource('downloads', \App\Http\Controllers\HOD\DownloadController::class)->except(['show']);
Route::get('downloads/{download}/file', [\App\Http\Controllers\HOD\DownloadController::class, 'file'])->name('downloads.file');
