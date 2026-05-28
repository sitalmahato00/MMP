<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Api\Controllers\AuthController;
use App\Modules\Public\Controllers\PublicApiController;
use App\Modules\Student\Controllers\Api\StudentApiController;
use App\Modules\Teacher\Controllers\Api\TeacherApiController;
use App\Modules\Exam\Controllers\Api\ExamApiController;
use App\Modules\Attendance\Controllers\Api\AttendanceApiController;
use App\Modules\Academic\Controllers\Api\AcademicApiController;
use App\Modules\Cms\Controllers\Api\CmsApiController;
use App\Modules\Cms\Controllers\Api\ExecutiveApiController;
use App\Modules\Api\Controllers\SubjectController;
use App\Modules\User\Controllers\Api\UserApiController;
use App\Modules\Parent\Controllers\Api\ParentApiController;
use App\Modules\Alumni\Controllers\Api\AlumniApiController;
use App\Modules\Staff\Controllers\Api\StaffApiController;
use App\Modules\Hod\Controllers\Api\HodApiController;

/*
|--------------------------------------------------------------------------
| API Routes — MMP ERP
|--------------------------------------------------------------------------
| All routes return JSON only (pure API mode).
| Auth: Laravel Sanctum bearer tokens.
*/

// ─── Authentication ───────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',           [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/otp/send',        [AuthController::class, 'sendOtp'])->middleware('throttle:3,1');
    Route::post('/otp/verify',      [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('/logout',          [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
});

// ─── Public API (unauthenticated) ─────────────────────────────────────────────
Route::prefix('v1/public')->middleware('throttle:public-api')->group(function () {
    Route::get('/homepage',           [PublicApiController::class, 'homepage']);
    Route::get('/notices',            [PublicApiController::class, 'notices']);
    Route::get('/notices/{slug}',     [PublicApiController::class, 'noticeShow']);
    Route::get('/departments',        [PublicApiController::class, 'departments']);
    Route::get('/departments/{deptSlug}/programs/{programSlug}', [PublicApiController::class, 'programShow']);
    Route::get('/departments/{slug}', [PublicApiController::class, 'departmentShow']);
    Route::get('/alumni-directory',   [PublicApiController::class, 'alumniDirectory']);
    Route::get('/alumni/{id}',        [PublicApiController::class, 'alumniProfile'])->whereNumber('id');
    Route::get('/alumni',             [PublicApiController::class, 'alumni']);
    Route::get('/downloads',          [PublicApiController::class, 'downloads']);
    Route::get('/pages/{slug}',       [PublicApiController::class, 'page']);
    Route::get('/facilities',         [PublicApiController::class, 'facilities']);
    Route::get('/staff',              [PublicApiController::class, 'staff']);
    Route::get('/leadership',         [PublicApiController::class, 'leadership']);
    Route::get('/site-settings',      [PublicApiController::class, 'siteSettings']);
    Route::get('/gallery',            [PublicApiController::class, 'gallery']);
    Route::get('/people',             [PublicApiController::class, 'people']);
    Route::get('/news-events',        [PublicApiController::class, 'newsEvents']);
    Route::get('/news-events/{slug}', [PublicApiController::class, 'newsEventShow']);
    Route::get('/question-bank',      [PublicApiController::class, 'questionBank']);
    Route::get('/result',             [PublicApiController::class, 'resultForm']);
});

// ─── Authenticated V1 API ─────────────────────────────────────────────────────
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Current authenticated user
    Route::get('/user', [AuthController::class, 'user']);

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/dashboard/stats', [AcademicApiController::class, 'dashboardStats']);
    Route::get('/dashboard/admin', [AcademicApiController::class, 'adminDashboard']);

    // ── Academic ──────────────────────────────────────────────────────────────
    Route::prefix('academic')->group(function () {
        Route::get('/sessions',              [AcademicApiController::class, 'sessions']);
        Route::get('/sessions/current',      [AcademicApiController::class, 'currentSession']);
        Route::post('/sessions',             [AcademicApiController::class, 'storeSession']);
        Route::get('/sessions/{session}',    [AcademicApiController::class, 'sessionShow']);
        Route::put('/sessions/{session}',    [AcademicApiController::class, 'updateSession']);
        Route::delete('/sessions/{session}', [AcademicApiController::class, 'destroySession']);
        Route::get('/programs',              [AcademicApiController::class, 'programs']);
        Route::post('/programs',             [AcademicApiController::class, 'storeProgram']);
        Route::get('/programs/{program}',    [AcademicApiController::class, 'programShow']);
        Route::put('/programs/{program}',    [AcademicApiController::class, 'updateProgram']);
        Route::delete('/programs/{program}', [AcademicApiController::class, 'destroyProgram']);
        Route::get('/subjects',              [AcademicApiController::class, 'subjects']);
    });

    // ── Departments ──────────────────────────────────────────────────────────
    Route::prefix('departments')->group(function () {
        Route::get('/',                          [AcademicApiController::class, 'departments']);
        Route::get('/{department}',              [AcademicApiController::class, 'departmentShow']);
        Route::post('/',                         [AcademicApiController::class, 'storeDepartment']);
        Route::put('/{department}',              [AcademicApiController::class, 'updateDepartment']);
        Route::delete('/{department}',           [AcademicApiController::class, 'destroyDepartment']);
    });

    // ── CMS (Notices) ────────────────────────────────────────────────────────
    Route::get('/notices', [CmsApiController::class, 'notices']);
    Route::get('/notices/{notice}', [CmsApiController::class, 'noticeShow']);
    Route::post('/notices', [CmsApiController::class, 'storeNotice']);
    Route::put('/notices/{notice}', [CmsApiController::class, 'updateNotice']);
    Route::delete('/notices/{notice}', [CmsApiController::class, 'destroyNotice']);

    // ── System Users ─────────────────────────────────────────────────────────
    Route::apiResource('users', UserApiController::class);

    // ── HODs ───────────────────────────────────────────────────────────────────
    Route::apiResource('hods', HodApiController::class);

    // ── Parents ────────────────────────────────────────────────────────────────
    Route::apiResource('parents', ParentApiController::class);

    // ── Alumni ─────────────────────────────────────────────────────────────────
    Route::put('/alumni/{alumnus}/toggle-featured', [AlumniApiController::class, 'toggleFeatured']);
    Route::apiResource('alumni', AlumniApiController::class);

    // ── Executives ─────────────────────────────────────────────────────────────
    Route::apiResource('executives', ExecutiveApiController::class);

    // ── Staff ──────────────────────────────────────────────────────────────────
    Route::put('/staff/{staff}/toggle-featured', [StaffApiController::class, 'toggleFeatured']);
    Route::put('/staff/{staff}/toggle-public', [StaffApiController::class, 'togglePublic']);
    Route::apiResource('staff', StaffApiController::class);

    // ── Students ──────────────────────────────────────────────────────────────
    Route::get('/students/export',        [StudentApiController::class, 'export']);
    Route::post('/students/{id}/restore', [StudentApiController::class, 'restore']);
    Route::apiResource('students', StudentApiController::class);

    // ── Teachers ──────────────────────────────────────────────────────────────
    Route::apiResource('teachers', TeacherApiController::class);

    // ── Exams ─────────────────────────────────────────────────────────────────
    Route::post('/exams/{exam}/publish', [ExamApiController::class, 'publish']);
    Route::get('/exams/{exam}/marks',    [ExamApiController::class, 'marks']);
    Route::post('/exams/{exam}/marks',   [ExamApiController::class, 'storeMarks']);
    Route::apiResource('exams', ExamApiController::class);

    // ── Attendance ────────────────────────────────────────────────────────────
    Route::get('/attendance/summary/{student}', [AttendanceApiController::class, 'studentSummary']);
    Route::apiResource('attendance', AttendanceApiController::class)->only(['index', 'store']);

    // ── Subjects ──────────────────────────────────────────────────────────────
    Route::get('/subjects/{subject}/students', [SubjectController::class, 'students']);
});
