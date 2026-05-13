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
            \App\Models\Student::class                              => \App\Modules\Student\Models\Student::class,
            \App\Services\StudentRecordService::class               => \App\Modules\Student\Services\StudentRecordService::class,

            // ── Teacher ─────────────────────────────────────────────────────
            \App\Models\Teacher::class                              => \App\Modules\Teacher\Models\Teacher::class,

            // ── Alumni ──────────────────────────────────────────────────────
            \App\Models\Alumni::class                               => \App\Modules\Alumni\Models\Alumni::class,
            \App\Models\AlumniAchievement::class                    => \App\Modules\Alumni\Models\AlumniAchievement::class,
            \App\Models\AlumniEmployment::class                     => \App\Modules\Alumni\Models\AlumniEmployment::class,
            \App\Models\AlumniProject::class                        => \App\Modules\Alumni\Models\AlumniProject::class,
            \App\Services\AlumniService::class                      => \App\Modules\Alumni\Services\AlumniService::class,

            // ── Academic ─────────────────────────────────────────────────────
            \App\Models\AcademicSession::class                      => \App\Modules\Academic\Models\AcademicSession::class,
            \App\Models\AcademicSessionSemester::class              => \App\Modules\Academic\Models\AcademicSessionSemester::class,
            \App\Models\Program::class                              => \App\Modules\Academic\Models\Program::class,
            \App\Models\Subject::class                              => \App\Modules\Academic\Models\Subject::class,
            \App\Models\Timetable::class                            => \App\Modules\Academic\Models\Timetable::class,
            \App\Models\TimetableSlot::class                        => \App\Modules\Academic\Models\TimetableSlot::class,
            \App\Services\SessionService::class                     => \App\Modules\Academic\Services\SessionService::class,

            // ── Attendance ───────────────────────────────────────────────────
            \App\Models\Attendance::class                           => \App\Modules\Attendance\Models\Attendance::class,
            \App\Models\AttendanceSession::class                    => \App\Modules\Attendance\Models\AttendanceSession::class,
            \App\Services\AttendanceService::class                  => \App\Modules\Attendance\Services\AttendanceService::class,

            // ── Exam / Result ────────────────────────────────────────────────
            \App\Models\Exam::class                                 => \App\Modules\Exam\Models\Exam::class,
            \App\Models\ExamSubjectMarkingScheme::class             => \App\Modules\Exam\Models\ExamSubjectMarkingScheme::class,
            \App\Models\Mark::class                                 => \App\Modules\Exam\Models\Mark::class,
            \App\Services\MarksService::class                       => \App\Modules\Exam\Services\MarksService::class,

            // ── Assignment ───────────────────────────────────────────────────
            \App\Models\Assignment::class                           => \App\Modules\Assignment\Models\Assignment::class,
            \App\Models\AssignmentSubmission::class                 => \App\Modules\Assignment\Models\AssignmentSubmission::class,

            // ── Department ───────────────────────────────────────────────────
            \App\Models\Department::class                           => \App\Modules\Department\Models\Department::class,

            // ── Parent ───────────────────────────────────────────────────────
            \App\Models\ParentModel::class                          => \App\Modules\Parent\Models\ParentModel::class,

            // ── Staff ────────────────────────────────────────────────────────
            \App\Models\Staff::class                                => \App\Modules\Staff\Models\Staff::class,
            \App\Models\StaffAttendance::class                      => \App\Modules\Staff\Models\StaffAttendance::class,
            \App\Models\StaffDocument::class                        => \App\Modules\Staff\Models\StaffDocument::class,

            // ── User / Auth ──────────────────────────────────────────────────
            \App\Models\User::class                                 => \App\Modules\User\Models\User::class,
            \App\Models\Otp::class                                  => \App\Modules\User\Models\Otp::class,
            \App\Services\OtpService::class                         => \App\Modules\User\Services\OtpService::class,

            // ── CMS ──────────────────────────────────────────────────────────
            \App\Models\Banner::class                               => \App\Modules\CMS\Models\Banner::class,
            \App\Models\Download::class                             => \App\Modules\CMS\Models\Download::class,
            \App\Models\Executive::class                            => \App\Modules\CMS\Models\Executive::class,
            \App\Models\Facility::class                             => \App\Modules\CMS\Models\Facility::class,
            \App\Models\Media::class                                => \App\Modules\CMS\Models\Media::class,
            \App\Models\Notice::class                               => \App\Modules\CMS\Models\Notice::class,
            \App\Models\NoticeAttachment::class                     => \App\Modules\CMS\Models\NoticeAttachment::class,
            \App\Models\Page::class                                 => \App\Modules\CMS\Models\Page::class,
            \App\Services\PublicDataService::class                  => \App\Modules\CMS\Services\PublicDataService::class,

            // ── Notification ─────────────────────────────────────────────────
            \App\Models\Communication::class                        => \App\Modules\Notification\Models\Communication::class,
            \App\Services\PortalNotificationService::class          => \App\Modules\Notification\Services\PortalNotificationService::class,
            \App\Services\NotificationPreferenceService::class      => \App\Modules\Notification\Services\NotificationPreferenceService::class,

            // ── Settings ─────────────────────────────────────────────────────
            \App\Models\SiteSetting::class                          => \App\Modules\Settings\Models\SiteSetting::class,

            // ── AuditLog ─────────────────────────────────────────────────────
            \App\Models\AuditLog::class                             => \App\Modules\AuditLog\Models\AuditLog::class,

            // ── File Manager ─────────────────────────────────────────────────
            \App\Services\ExportService::class                      => \App\Modules\FileManager\Services\ExportService::class,
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
