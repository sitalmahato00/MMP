<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PublicApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\HodController;
use App\Http\Controllers\Api\AlumniController;
use App\Http\Controllers\Api\ManagementController;
use App\Http\Controllers\Api\AdminController;

// ═══════════════════════════════════════════════════════════════════════════
// AUTHENTICATION ROUTES (Public - No Auth Required)
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('auth')->middleware('throttle:3,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Token refresh and logout (require authentication)
Route::post('/auth/refresh-token', [AuthController::class, 'refreshToken'])->middleware('auth:sanctum');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ═══════════════════════════════════════════════════════════════════════════
// API v1 ROUTES (Sanctum authenticated)
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('v1')->group(function () {

    // ───────────────────────────────────────────────────────────────────────
    // PUBLIC API — No Authentication Required
    // ───────────────────────────────────────────────────────────────────────
    Route::prefix('public')->middleware('throttle:public-api')->group(function () {
        Route::get('/homepage', [PublicApiController::class, 'homepage']);
        Route::get('/notices', [PublicApiController::class, 'notices']);
        Route::get('/departments', [PublicApiController::class, 'departments']);
        Route::get('/departments/{slug}', [PublicApiController::class, 'departmentShow']);
        Route::get('/alumni', [PublicApiController::class, 'alumni']);
        Route::get('/downloads', [PublicApiController::class, 'downloads']);
        Route::get('/pages/{slug}', [PublicApiController::class, 'page']);
        Route::get('/facilities', [PublicApiController::class, 'facilities']);
        Route::get('/staff', [PublicApiController::class, 'staff']);
        Route::get('/leadership', [PublicApiController::class, 'leadership']);
        Route::get('/site-settings', [PublicApiController::class, 'siteSettings']);
    });

    // ───────────────────────────────────────────────────────────────────────
    // AUTHENTICATED ROUTES (All users - require valid token)
    // ───────────────────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        // User profile and auth info
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user/profile', [AuthController::class, 'updateProfile']);
        Route::post('/user/change-password', [AuthController::class, 'changePassword']);
        Route::put('/user/notification-preferences', [AuthController::class, 'updateNotificationPreferences']);
        Route::put('/user/two-factor', [AuthController::class, 'updateTwoFactor']);

        // ───────────────────────────────────────────────────────────────────
        // STUDENT MODULE (Role: student)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('student')->middleware('role:student')->group(function () {
            // Dashboard
            Route::get('/dashboard', [StudentController::class, 'dashboard']);

            // Attendance
            Route::prefix('attendance')->group(function () {
                Route::get('/summary', [StudentController::class, 'attendanceSummary']);
                Route::get('/detail', [StudentController::class, 'attendanceDetail']);
                Route::get('/by-subject/{subject}', [StudentController::class, 'attendanceBySubject']);
            });

            // Marks & Exam Results
            Route::prefix('marks')->group(function () {
                Route::get('/summary', [StudentController::class, 'marksSummary']);
                Route::get('/exam/{exam}', [StudentController::class, 'examMarks']);
                Route::get('/subject/{subject}', [StudentController::class, 'subjectMarks']);
                Route::get('/marksheet', [StudentController::class, 'downloadMarksheet']);
            });

            // Subjects
            Route::get('/subjects', [StudentController::class, 'subjects']);
            Route::get('/subjects/{subject}', [StudentController::class, 'subjectDetail']);

            // Assignments
            Route::prefix('assignments')->group(function () {
                Route::get('/', [StudentController::class, 'assignments']);
                Route::get('/{assignment}', [StudentController::class, 'assignmentDetail']);
                Route::post('/{assignment}/submit', [StudentController::class, 'submitAssignment']);
                Route::get('/{submission}/submission-status', [StudentController::class, 'submissionStatus']);
            });

            // Timetable
            Route::get('/timetable', [StudentController::class, 'timetable']);
            Route::get('/timetable/{day}', [StudentController::class, 'timetableByDay']);

            // Study Materials & Downloads
            Route::prefix('downloads')->group(function () {
                Route::get('/', [StudentController::class, 'downloads']);
                Route::get('/{download}/file', [StudentController::class, 'downloadFile']);
            });

            // Notices
            Route::prefix('notices')->group(function () {
                Route::get('/', [StudentController::class, 'notices']);
                Route::get('/{notice}', [StudentController::class, 'noticeDetail']);
                Route::get('/filter/{category}', [StudentController::class, 'noticesByCategory']);
            });

            // Profile
            Route::get('/profile', [StudentController::class, 'profile']);
            Route::put('/profile', [StudentController::class, 'updateProfile']);
        });

        // ───────────────────────────────────────────────────────────────────
        // TEACHER MODULE (Role: teacher)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('teacher')->middleware('role:teacher')->group(function () {
            // Dashboard
            Route::get('/dashboard', [TeacherController::class, 'dashboard']);

            // Today's Classes
            Route::get('/today-schedule', [TeacherController::class, 'todaySchedule']);
            Route::get('/classes', [TeacherController::class, 'classes']);

            // Attendance Management
            Route::prefix('attendance')->group(function () {
                Route::get('/session/{session}', [TeacherController::class, 'attendanceSession']);
                Route::post('/mark', [TeacherController::class, 'markAttendance']);
                Route::post('/bulk-mark', [TeacherController::class, 'bulkMarkAttendance']);
                Route::get('/history', [TeacherController::class, 'attendanceHistory']);
            });

            // Marks Entry
            Route::prefix('marks')->group(function () {
                Route::get('/components/{subject}', [TeacherController::class, 'markComponents']);
                Route::post('/submit', [TeacherController::class, 'submitMarks']);
                Route::get('/pending', [TeacherController::class, 'pendingMarks']);
                Route::get('/history', [TeacherController::class, 'marksHistory']);
            });

            // Assignment Management
            Route::prefix('assignments')->group(function () {
                Route::get('/', [TeacherController::class, 'assignments']);
                Route::post('/create', [TeacherController::class, 'createAssignment']);
                Route::put('/{assignment}', [TeacherController::class, 'updateAssignment']);
                Route::delete('/{assignment}', [TeacherController::class, 'deleteAssignment']);
                Route::get('/{assignment}/submissions', [TeacherController::class, 'assignmentSubmissions']);
                Route::post('/{submission}/grade', [TeacherController::class, 'gradeSubmission']);
            });

            // Student List & Sections
            Route::get('/students', [TeacherController::class, 'students']);
            Route::get('/students/{subject}', [TeacherController::class, 'studentsBySubject']);
            Route::get('/sections', [TeacherController::class, 'sections']);

            // Timetable
            Route::get('/timetable', [TeacherController::class, 'timetable']);

            // Reports
            Route::get('/reports/attendance', [TeacherController::class, 'attendanceReport']);
            Route::get('/reports/marks', [TeacherController::class, 'marksReport']);

            // Profile
            Route::get('/profile', [TeacherController::class, 'profile']);
            Route::put('/profile', [TeacherController::class, 'updateProfile']);
        });

        // ───────────────────────────────────────────────────────────────────
        // PARENT MODULE (Role: parent)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('parent')->middleware('role:parent')->group(function () {
            // Dashboard
            Route::get('/dashboard', [ParentController::class, 'dashboard']);

            // Children Management
            Route::get('/children', [ParentController::class, 'children']);
            Route::get('/children/{child}', [ParentController::class, 'childDetail']);

            // Child Attendance Monitoring
            Route::prefix('child/{child}/attendance')->group(function () {
                Route::get('/', [ParentController::class, 'childAttendance']);
                Route::get('/summary', [ParentController::class, 'childAttendanceSummary']);
                Route::get('/by-subject/{subject}', [ParentController::class, 'childAttendanceBySubject']);
            });

            // Child Marks Monitoring
            Route::prefix('child/{child}/marks')->group(function () {
                Route::get('/', [ParentController::class, 'childMarks']);
                Route::get('/summary', [ParentController::class, 'childMarksSummary']);
                Route::get('/exam/{exam}', [ParentController::class, 'childExamMarks']);
                Route::get('/marksheet', [ParentController::class, 'childMarksheet']);
            });

            // Child Assignment Monitoring
            Route::prefix('child/{child}/assignments')->group(function () {
                Route::get('/', [ParentController::class, 'childAssignments']);
                Route::get('/{assignment}', [ParentController::class, 'childAssignmentDetail']);
            });

            // Notices
            Route::get('/notices', [ParentController::class, 'notices']);
            Route::get('/notices/{notice}', [ParentController::class, 'noticeDetail']);

            // Child Timetable
            Route::get('/child/{child}/timetable', [ParentController::class, 'childTimetable']);

            // Profile
            Route::get('/profile', [ParentController::class, 'profile']);
            Route::put('/profile', [ParentController::class, 'updateProfile']);
        });

        // ───────────────────────────────────────────────────────────────────
        // HEAD OF DEPARTMENT (HOD) MODULE (Role: hod)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('hod')->middleware('role:hod')->group(function () {
            // Dashboard
            Route::get('/dashboard', [HodController::class, 'dashboard']);

            // Department Overview
            Route::get('/department', [HodController::class, 'departmentOverview']);
            Route::get('/statistics', [HodController::class, 'departmentStatistics']);

            // Student Management
            Route::prefix('students')->group(function () {
                Route::get('/', [HodController::class, 'students']);
                Route::get('/{student}', [HodController::class, 'studentDetail']);
                Route::get('/{student}/attendance', [HodController::class, 'studentAttendance']);
                Route::get('/{student}/marks', [HodController::class, 'studentMarks']);
            });

            // Teacher Management
            Route::prefix('teachers')->group(function () {
                Route::get('/', [HodController::class, 'teachers']);
                Route::get('/{teacher}', [HodController::class, 'teacherDetail']);
                Route::get('/{teacher}/subjects', [HodController::class, 'teacherSubjects']);
            });

            // Subject Management
            Route::get('/subjects', [HodController::class, 'subjects']);
            Route::get('/subjects/{subject}', [HodController::class, 'subjectDetail']);

            // Reports
            Route::prefix('reports')->group(function () {
                Route::get('/attendance', [HodController::class, 'attendanceReport']);
                Route::get('/marks', [HodController::class, 'marksReport']);
                Route::get('/performance', [HodController::class, 'performanceReport']);
                Route::get('/assignments', [HodController::class, 'assignmentReport']);
            });

            // Academic Sessions
            Route::get('/sessions', [HodController::class, 'sessions']);
        });

        // ───────────────────────────────────────────────────────────────────
        // ALUMNI MODULE (Role: alumni)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('alumni')->middleware('role:alumni')->group(function () {
            // Dashboard
            Route::get('/dashboard', [AlumniController::class, 'dashboard']);

            // Academic Records
            Route::prefix('records')->group(function () {
                Route::get('/marksheets', [AlumniController::class, 'marksheets']);
                Route::get('/marksheet/{marksheet}', [AlumniController::class, 'marksheetDetail']);
                Route::get('/transcripts', [AlumniController::class, 'transcripts']);
                Route::get('/transcript/{transcript}', [AlumniController::class, 'transcriptDetail']);
            });

            // Documents
            Route::prefix('documents')->group(function () {
                Route::get('/', [AlumniController::class, 'documents']);
                Route::get('/{document}', [AlumniController::class, 'documentDetail']);
                Route::post('/{document}/download', [AlumniController::class, 'downloadDocument']);
            });

            // Alumni Notices
            Route::get('/notices', [AlumniController::class, 'notices']);
            Route::get('/notices/{notice}', [AlumniController::class, 'noticeDetail']);

            // Profile
            Route::get('/profile', [AlumniController::class, 'profile']);
            Route::put('/profile', [AlumniController::class, 'updateProfile']);

            // Alumni Network
            Route::get('/alumni-list', [AlumniController::class, 'alumniList']);
        });

        // ───────────────────────────────────────────────────────────────────
        // ADMIN MODULE (Role: admin) - Full CRUD Management
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::get('/audit-logs', [AdminController::class, 'auditLogs']);

            // Teacher Management
            Route::prefix('teachers')->group(function () {
                Route::get('/', [ManagementController::class, 'teachersIndex']);
                Route::get('/{id}', [ManagementController::class, 'teachersShow']);
                Route::post('/', [ManagementController::class, 'teachersStore']);
                Route::put('/{id}', [ManagementController::class, 'teachersUpdate']);
                Route::delete('/{id}', [ManagementController::class, 'teachersDestroy']);
            });

            // Student Management
            Route::prefix('students')->group(function () {
                Route::get('/', [ManagementController::class, 'studentsIndex']);
                Route::get('/{id}', [ManagementController::class, 'studentsShow']);
                Route::post('/', [ManagementController::class, 'studentsStore']);
                Route::put('/{id}', [ManagementController::class, 'studentsUpdate']);
                Route::delete('/{id}', [ManagementController::class, 'studentsDestroy']);
            });

            // Parent Management
            Route::prefix('parents')->group(function () {
                Route::get('/', [ManagementController::class, 'parentsIndex']);
                Route::get('/{id}', [ManagementController::class, 'parentsShow']);
                Route::post('/', [ManagementController::class, 'parentsStore']);
                Route::put('/{id}', [ManagementController::class, 'parentsUpdate']);
                Route::delete('/{id}', [ManagementController::class, 'parentsDestroy']);
            });
        });
    });
});
