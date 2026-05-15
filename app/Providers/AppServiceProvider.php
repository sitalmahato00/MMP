<?php

namespace App\Providers;

use App\Modules\CMS\Services\PublicDataService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ── Module class aliases ────────────────────────────────────────────
        // These aliases allow legacy code using App\Models\* or App\Services\*
        // to keep working while the codebase migrates to the modular structure.
        $this->registerModuleAliases();
    }

    /**
     * Map old fully-qualified class names to their new module locations.
     * Add a new entry here each time a class is moved into a module.
     */
    private function registerModuleAliases(): void
    {
        $aliases = [
            // ── Student ─────────────────────────────────────────────────────
            'App\Models\Student'                              => \App\Modules\Student\Models\Student::class,
            'App\Services\StudentRecordService'               => \App\Modules\Student\Services\StudentRecordService::class,

            // ── Teacher ─────────────────────────────────────────────────────
            'App\Models\Teacher'                              => \App\Modules\Teacher\Models\Teacher::class,

            // ── Alumni ──────────────────────────────────────────────────────
            'App\Models\Alumni'                               => \App\Modules\Alumni\Models\Alumni::class,
            'App\Models\AlumniAchievement'                    => \App\Modules\Alumni\Models\AlumniAchievement::class,
            'App\Models\AlumniEmployment'                     => \App\Modules\Alumni\Models\AlumniEmployment::class,
            'App\Models\AlumniProject'                        => \App\Modules\Alumni\Models\AlumniProject::class,
            'App\Services\AlumniService'                      => \App\Modules\Alumni\Services\AlumniService::class,

            // ── Academic ─────────────────────────────────────────────────────
            'App\Models\AcademicSession'                      => \App\Modules\Academic\Models\AcademicSession::class,
            'App\Models\AcademicSessionSemester'              => \App\Modules\Academic\Models\AcademicSessionSemester::class,
            'App\Models\Program'                              => \App\Modules\Academic\Models\Program::class,
            'App\Models\Subject'                              => \App\Modules\Academic\Models\Subject::class,
            'App\Models\Timetable'                            => \App\Modules\Academic\Models\Timetable::class,
            'App\Models\TimetableSlot'                        => \App\Modules\Academic\Models\TimetableSlot::class,
            'App\Services\SessionService'                     => \App\Modules\Academic\Services\SessionService::class,

            // ── Attendance ───────────────────────────────────────────────────
            'App\Models\Attendance'                           => \App\Modules\Attendance\Models\Attendance::class,
            'App\Models\AttendanceSession'                    => \App\Modules\Attendance\Models\AttendanceSession::class,
            'App\Services\AttendanceService'                  => \App\Modules\Attendance\Services\AttendanceService::class,

            // ── Exam / Result ────────────────────────────────────────────────
            'App\Models\Exam'                                 => \App\Modules\Exam\Models\Exam::class,
            'App\Models\ExamSubjectMarkingScheme'             => \App\Modules\Exam\Models\ExamSubjectMarkingScheme::class,
            'App\Models\Mark'                                 => \App\Modules\Exam\Models\Mark::class,
            'App\Services\MarksService'                       => \App\Modules\Exam\Services\MarksService::class,

            // ── Assignment ───────────────────────────────────────────────────
            'App\Models\Assignment'                           => \App\Modules\Assignment\Models\Assignment::class,
            'App\Models\AssignmentSubmission'                 => \App\Modules\Assignment\Models\AssignmentSubmission::class,

            // ── Department ───────────────────────────────────────────────────
            'App\Models\Department'                           => \App\Modules\Department\Models\Department::class,

            // ── Parent ───────────────────────────────────────────────────────
            'App\Models\ParentModel'                          => \App\Modules\Parent\Models\ParentModel::class,

            // ── Staff ────────────────────────────────────────────────────────
            'App\Models\Staff'                                => \App\Modules\Staff\Models\Staff::class,
            'App\Models\StaffAttendance'                      => \App\Modules\Staff\Models\StaffAttendance::class,
            'App\Models\StaffDocument'                        => \App\Modules\Staff\Models\StaffDocument::class,

            // ── User / Auth ──────────────────────────────────────────────────
            'App\Models\User'                                 => \App\Modules\User\Models\User::class,
            'App\Models\Otp'                                  => \App\Modules\User\Models\Otp::class,
            'App\Services\OtpService'                         => \App\Modules\User\Services\OtpService::class,

            // ── CMS ──────────────────────────────────────────────────────────
            'App\Models\Banner'                               => \App\Modules\CMS\Models\Banner::class,
            'App\Models\Download'                             => \App\Modules\CMS\Models\Download::class,
            'App\Models\Executive'                            => \App\Modules\CMS\Models\Executive::class,
            'App\Models\Facility'                             => \App\Modules\CMS\Models\Facility::class,
            'App\Models\Media'                                => \App\Modules\CMS\Models\Media::class,
            'App\Models\Notice'                               => \App\Modules\CMS\Models\Notice::class,
            'App\Models\NoticeAttachment'                     => \App\Modules\CMS\Models\NoticeAttachment::class,
            'App\Models\Page'                                 => \App\Modules\CMS\Models\Page::class,
            'App\Services\PublicDataService'                  => \App\Modules\CMS\Services\PublicDataService::class,

            // ── Notification ─────────────────────────────────────────────────
            'App\Models\Communication'                        => \App\Modules\Notification\Models\Communication::class,
            'App\Services\PortalNotificationService'          => \App\Modules\Notification\Services\PortalNotificationService::class,
            'App\Services\NotificationPreferenceService'      => \App\Modules\Notification\Services\NotificationPreferenceService::class,

            // ── Settings ─────────────────────────────────────────────────────
            'App\Models\SiteSetting'                          => \App\Modules\Settings\Models\SiteSetting::class,

            // ── AuditLog ─────────────────────────────────────────────────────
            'App\Models\AuditLog'                             => \App\Modules\AuditLog\Models\AuditLog::class,

            // ── File Manager ─────────────────────────────────────────────────
            'App\Services\ExportService'                      => \App\Modules\FileManager\Services\ExportService::class,
        ];

        foreach ($aliases as $old => $new) {
            if (! class_exists($old) && class_exists($new)) {
                class_alias($new, $old);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Morph map: keep DB model_type strings pointing to their new module classes.
        // Spatie permissions stores 'App\Models\User' in model_has_roles / model_has_permissions.
        // Without this map, getMorphClass() returns the new FQCN and hasRole() finds nothing.
        Relation::morphMap([
            'App\Models\User'    => \App\Modules\User\Models\User::class,
            'App\Models\Student' => \App\Modules\Student\Models\Student::class,
            'App\Models\Teacher' => \App\Modules\Teacher\Models\Teacher::class,
            'App\Models\Alumni'  => \App\Modules\Alumni\Models\Alumni::class,
            'App\Models\Staff'   => \App\Modules\Staff\Models\Staff::class,
        ]);

        Paginator::defaultView('vendor.pagination.custom');

        // API rate limiter (default for API routes)
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $email = strtolower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email !== '' ? $email.'|'.$request->ip() : $request->ip());
        });

        RateLimiter::for('apply', function (Request $request): Limit {
            $email = strtolower(trim((string) $request->input('email')));

            return Limit::perHour(10)->by($email !== '' ? $email.'|'.$request->ip() : $request->ip());
        });

        RateLimiter::for('result-check', function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('public-api', function (Request $request): Limit {
            return Limit::perMinute(120)->by($request->ip());
        });

        View::composer(['layouts.guest', 'components.sidebar', 'auth.login'], function ($view): void {
            $publicCourses = collect();

            if (Schema::hasTable('departments')) {
                $publicCourses = app(PublicDataService::class)->getNavigationCourses();
            }

            $view->with('publicCourses', $publicCourses);
        });
    }
}
