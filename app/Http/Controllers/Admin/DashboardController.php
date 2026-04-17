<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\Student;
use App\Services\PublicDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct(private PublicDataService $publicDataService)
    {
    }

    public function index(Request $request)
    {
        $period = $this->resolvePeriod($request->string('period')->toString());

        $sessionOptions = Cache::remember('admin_dashboard_sessions', 600, function () {
            return AcademicSession::query()
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->get(['id', 'name', 'name_bs', 'start_date', 'end_date', 'is_active', 'status']);
        });

        $activeSession = AcademicSession::current();
        $selectedSession = $period === 'session'
            ? $this->resolveSession($request->integer('session_id'), $sessionOptions, $activeSession)
            : $activeSession;

        $window = $this->resolveWindow($period, $selectedSession);
        $comparison = $this->resolveComparisonWindow($period, $window['start'], $window['end'], $selectedSession);

        $cacheKey = sprintf('admin_dashboard_modern:%s:%s', $period, $period === 'session' ? ($selectedSession?->id ?? 'none') : 'default');

        $payload = Cache::remember($cacheKey, 300, function () use ($period, $window, $comparison, $selectedSession, $activeSession) {
            $currentAttendanceRecords = $this->loadAttendanceRecords($window['start'], $window['end'], $period === 'session' ? $selectedSession : null);
            $previousAttendanceRecords = $this->loadAttendanceRecords($comparison['start'], $comparison['end'], $comparison['session']);

            $currentMarks = $this->loadPublishedMarks($window['start'], $window['end'], $period === 'session' ? $selectedSession : null);
            $previousMarks = $this->loadPublishedMarks($comparison['start'], $comparison['end'], $comparison['session']);

            $currentAdmissions = $this->countAdmissions($window['start'], $window['end'], $period === 'session' ? $selectedSession : null);
            $previousAdmissions = $this->countAdmissions($comparison['start'], $comparison['end'], $comparison['session']);

            $currentApplicationsVolume = $this->countApplications($window['start'], $window['end']);
            $previousApplicationsVolume = $this->countApplications($comparison['start'], $comparison['end']);

            $currentStudents = Student::active()->count();
            $pendingApplications = Application::where('status', 'pending')->count();

            $attendanceSummary = $this->summarizeAttendance($currentAttendanceRecords);
            $previousAttendanceSummary = $this->summarizeAttendance($previousAttendanceRecords);

            $passSummary = $this->summarizeMarks($currentMarks);
            $previousPassSummary = $this->summarizeMarks($previousMarks);

            $departmentPerformance = $this->buildDepartmentPerformance($currentAttendanceRecords, $currentMarks);
            $enrollmentTrend = $this->buildEnrollmentTrend($window['start'], $window['end'], $period, $selectedSession);

            $currentAdmissionsTrend = $this->formatTrend($currentAdmissions, $previousAdmissions);
            $attendanceTrend = $this->formatTrend($attendanceSummary['rate'], $previousAttendanceSummary['rate']);
            $passTrend = $this->formatTrend($passSummary['rate'], $previousPassSummary['rate']);
            $applicationTrend = $this->formatTrend($currentApplicationsVolume, $previousApplicationsVolume);

            $recentNotices = Notice::published()
                ->with(['author', 'department'])
                ->latest()
                ->take(5)
                ->get();

            $recentApplications = Application::with('department')
                ->latest()
                ->take(5)
                ->get();

            $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(5);
            $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(5);

            $alerts = $this->buildAlerts(
                $departmentPerformance['rows'],
                $attendanceSummary,
                $passSummary,
                $pendingApplications,
                $currentApplicationsVolume
            );

            $highlight = $this->buildHighlight(
                $departmentPerformance['top'],
                $currentStudents,
                $currentAdmissions,
                $currentApplicationsVolume,
                $passSummary['rate'],
                $attendanceSummary['rate']
            );

            $kpiCards = [
                [
                    'key' => 'students',
                    'title' => 'Total Students',
                    'value' => number_format($currentStudents),
                    'suffix' => null,
                    'trend' => $currentAdmissionsTrend['text'],
                    'trendDirection' => $currentAdmissionsTrend['direction'],
                    'note' => number_format($currentAdmissions) . ' admissions in ' . $window['label'],
                    'icon' => 'students',
                    'tone' => 'red',
                    'href' => route('admin.students.index'),
                ],
                [
                    'key' => 'attendance',
                    'title' => 'Attendance Rate',
                    'value' => number_format($attendanceSummary['rate'], 1),
                    'suffix' => '%',
                    'trend' => $attendanceTrend['text'],
                    'trendDirection' => $attendanceTrend['direction'],
                    'note' => number_format($attendanceSummary['present']) . ' / ' . number_format($attendanceSummary['total']) . ' records',
                    'icon' => 'attendance',
                    'tone' => 'amber',
                    'href' => route('admin.dashboard', ['period' => $period]) . '#main-insights',
                ],
                [
                    'key' => 'pass',
                    'title' => 'Pass Rate',
                    'value' => number_format($passSummary['rate'], 1),
                    'suffix' => '%',
                    'trend' => $passTrend['text'],
                    'trendDirection' => $passTrend['direction'],
                    'note' => number_format($passSummary['passed']) . ' / ' . number_format($passSummary['total']) . ' published marks',
                    'icon' => 'results',
                    'tone' => 'green',
                    'href' => route('admin.exams.index'),
                ],
                [
                    'key' => 'applications',
                    'title' => 'Active Applications',
                    'value' => number_format($pendingApplications),
                    'suffix' => null,
                    'trend' => $applicationTrend['text'],
                    'trendDirection' => $applicationTrend['direction'],
                    'note' => number_format($currentApplicationsVolume) . ' submissions in ' . $window['label'],
                    'icon' => 'applications',
                    'tone' => 'slate',
                    'href' => route('admin.applications.index'),
                ],
            ];

            return [
                'kpiCards' => $kpiCards,
                'chartData' => [
                    'enrollment' => $enrollmentTrend,
                    'departmentPerformance' => $departmentPerformance,
                ],
                'alerts' => $alerts,
                'highlight' => $highlight,
                'recentNotices' => $recentNotices,
                'recentApplications' => $recentApplications,
                'ctevtGeneralNotices' => $ctevtGeneralNotices,
                'ctevtResultNotices' => $ctevtResultNotices,
                'currentStudents' => $currentStudents,
                'pendingApplications' => $pendingApplications,
                'attendanceSummary' => $attendanceSummary,
                'passSummary' => $passSummary,
                'activeSession' => $activeSession,
            ];
        });

        $dashboardState = $this->buildDashboardState($payload, $period, $selectedSession, $sessionOptions, $window);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($dashboardState);
        }

        return view('admin.dashboard', array_merge($payload, [
            'greeting' => $this->greeting(),
            'period' => $period,
            'periodLabel' => $window['label'],
            'rangeStart' => $window['start'],
            'rangeEnd' => $window['end'],
            'selectedSession' => $selectedSession,
            'sessionOptions' => $sessionOptions,
            'periodOptions' => [
                ['value' => 'week', 'label' => 'Week', 'hint' => 'Last 7 days'],
                ['value' => 'month', 'label' => 'Month', 'hint' => 'Last 30 days'],
                ['value' => 'session', 'label' => 'Session', 'hint' => 'Full academic session'],
            ],
            'lastUpdated' => now(),
            'dashboardState' => $dashboardState,
            'ctevtGeneralNotices' => $payload['ctevtGeneralNotices'],
            'ctevtResultNotices' => $payload['ctevtResultNotices'],
        ]));
    }

    private function resolvePeriod(string $period): string
    {
        return in_array($period, ['week', 'month', 'session'], true) ? $period : 'month';
    }

    private function resolveSession(?int $sessionId, Collection $sessions, ?AcademicSession $fallback): ?AcademicSession
    {
        if ($sessionId) {
            $matched = $sessions->firstWhere('id', $sessionId);
            if ($matched) {
                return $matched;
            }
        }

        return $fallback ?? $sessions->first();
    }

    private function resolveWindow(string $period, ?AcademicSession $session): array
    {
        $now = Carbon::now();

        if ($period === 'week') {
            return [
                'start' => $now->copy()->subDays(6)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'label' => 'Last 7 days',
                'bucket' => 'day',
            ];
        }

        if ($period === 'session') {
            $start = $session?->start_date ? Carbon::parse($session->start_date)->startOfDay() : $now->copy()->subDays(29)->startOfDay();
            $end = $session?->end_date ? Carbon::parse($session->end_date)->endOfDay() : $now->copy()->endOfDay();

            return [
                'start' => $start,
                'end' => $end,
                'label' => $session?->name ?? 'Selected session',
                'bucket' => 'month',
            ];
        }

        return [
            'start' => $now->copy()->subDays(29)->startOfDay(),
            'end' => $now->copy()->endOfDay(),
            'label' => 'Last 30 days',
            'bucket' => 'day',
        ];
    }

    private function resolveComparisonWindow(string $period, Carbon $start, Carbon $end, ?AcademicSession $session): array
    {
        if ($period === 'session' && $session?->start_date && $session?->end_date) {
            $previousSession = AcademicSession::query()
                ->whereNotNull('end_date')
                ->where('end_date', '<', $session->start_date)
                ->orderByDesc('end_date')
                ->first();

            if ($previousSession?->start_date && $previousSession?->end_date) {
                return [
                    'start' => Carbon::parse($previousSession->start_date)->startOfDay(),
                    'end' => Carbon::parse($previousSession->end_date)->endOfDay(),
                    'session' => $previousSession,
                ];
            }
        }

        $days = max($start->diffInDays($end) + 1, 1);

        return [
            'start' => $start->copy()->subDays($days)->startOfDay(),
            'end' => $start->copy()->subDay()->endOfDay(),
            'session' => null,
        ];
    }

    private function loadAttendanceRecords(Carbon $start, Carbon $end, ?AcademicSession $session): Collection
    {
        return Attendance::query()
            ->with(['student.department', 'attendanceSession'])
            ->when($session, function ($query) use ($session) {
                $query->whereHas('attendanceSession', fn ($attendanceSession) => $attendanceSession->where('academic_session_id', $session->id));
            }, function ($query) use ($start, $end) {
                $query->whereHas('attendanceSession', fn ($attendanceSession) => $attendanceSession->whereBetween('date', [$start->toDateString(), $end->toDateString()]));
            })
            ->get();
    }

    private function loadPublishedMarks(Carbon $start, Carbon $end, ?AcademicSession $session): Collection
    {
        return Mark::query()
            ->published()
            ->with(['subject', 'student.department', 'exam'])
            ->when($session, function ($query) use ($session) {
                $query->whereHas('exam', fn ($exam) => $exam->where('academic_session_id', $session->id));
            }, function ($query) use ($start, $end) {
                $query->whereBetween('updated_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
            })
            ->get();
    }

    private function countAdmissions(Carbon $start, Carbon $end, ?AcademicSession $session): int
    {
        return Student::query()
            ->when($session, fn ($query) => $query->where('academic_session_id', $session->id), fn ($query) => $query->whereBetween('admission_date', [$start->toDateString(), $end->toDateString()]))
            ->whereNotNull('admission_date')
            ->count();
    }

    private function countApplications(Carbon $start, Carbon $end): int
    {
        return Application::query()
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();
    }

    private function summarizeAttendance(Collection $records): array
    {
        $total = $records->count();
        $present = $records->where('status', 'present')->count();

        return [
            'total' => $total,
            'present' => $present,
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
        ];
    }

    private function summarizeMarks(Collection $marks): array
    {
        $total = $marks->count();
        $passed = $marks->filter(fn ($mark) => $mark->is_passed)->count();

        return [
            'total' => $total,
            'passed' => $passed,
            'rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0.0,
        ];
    }

    private function buildEnrollmentTrend(Carbon $start, Carbon $end, string $period, ?AcademicSession $session): array
    {
        $bucketType = $period === 'session' ? 'month' : 'day';
        $buckets = $this->makeBuckets($start, $end, $bucketType);

        $students = Student::query()
            ->whereNotNull('admission_date')
            ->when($session, fn ($query) => $query->where('academic_session_id', $session->id), fn ($query) => $query->whereBetween('admission_date', [$start->toDateString(), $end->toDateString()]))
            ->get(['admission_date']);

        foreach ($students as $student) {
            $admissionDate = Carbon::parse($student->admission_date);
            $bucketKey = $bucketType === 'month' ? $admissionDate->format('Y-m') : $admissionDate->format('Y-m-d');

            if (isset($buckets[$bucketKey])) {
                $buckets[$bucketKey]['value']++;
            }
        }

        return [
            'labels' => array_values(array_map(static fn (array $bucket) => $bucket['label'], $buckets)),
            'values' => array_values(array_map(static fn (array $bucket) => $bucket['value'], $buckets)),
        ];
    }

    private function buildDepartmentPerformance(Collection $attendanceRecords, Collection $marks): array
    {
        $departments = Department::active()
            ->withCount('students')
            ->get();

        $rows = $departments->map(function (Department $department) use ($attendanceRecords, $marks) {
            $deptAttendance = $attendanceRecords->filter(function ($record) use ($department) {
                return (int) data_get($record, 'student.department_id') === (int) $department->id;
            });

            $deptMarks = $marks->filter(function ($mark) use ($department) {
                return (int) data_get($mark, 'student.department_id') === (int) $department->id;
            });

            $attendanceRate = $deptAttendance->count() > 0
                ? round(($deptAttendance->where('status', 'present')->count() / $deptAttendance->count()) * 100, 1)
                : null;

            $passRate = $deptMarks->count() > 0
                ? round(($deptMarks->filter(fn ($mark) => $mark->is_passed)->count() / $deptMarks->count()) * 100, 1)
                : null;

            $score = match (true) {
                $attendanceRate !== null && $passRate !== null => round(($attendanceRate * 0.45) + ($passRate * 0.55), 1),
                $attendanceRate !== null => $attendanceRate,
                $passRate !== null => $passRate,
                default => 0.0,
            };

            return [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'label' => $department->code ?: Str::limit($department->name, 16),
                'students' => (int) ($department->students_count ?? 0),
                'attendance_rate' => $attendanceRate,
                'pass_rate' => $passRate,
                'score' => $score,
                'has_data' => $deptAttendance->isNotEmpty() || $deptMarks->isNotEmpty(),
            ];
        })->sortByDesc('score')->values();

        return [
            'rows' => $rows,
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('score')->all(),
            'top' => $rows->first(),
            'hasData' => $rows->contains(fn (array $row) => $row['has_data']),
        ];
    }

    private function buildAlerts(Collection $departmentRows, array $attendanceSummary, array $passSummary, int $pendingApplications, int $applicationVolume): array
    {
        $alerts = [];

        $lowAttendance = $departmentRows->first(fn (array $row) => $row['attendance_rate'] !== null && $row['attendance_rate'] < 75);
        if ($lowAttendance) {
            $alerts[] = [
                'tone' => 'danger',
                'title' => 'Low attendance in ' . $lowAttendance['name'],
                'message' => 'Attendance is ' . number_format($lowAttendance['attendance_rate'], 1) . '% in the selected window. Review class engagement and follow up early.',
                'actionLabel' => 'Open Students',
                'actionHref' => route('admin.students.index'),
            ];
        }

        if ($pendingApplications >= 10) {
            $alerts[] = [
                'tone' => 'warning',
                'title' => number_format($pendingApplications) . ' applications awaiting review',
                'message' => 'The admissions queue is growing. Clear the review backlog to avoid response delays.',
                'actionLabel' => 'Review Applications',
                'actionHref' => route('admin.applications.index'),
            ];
        }

        if ($passSummary['total'] > 0 && $passSummary['rate'] < 70) {
            $alerts[] = [
                'tone' => 'warning',
                'title' => 'Pass rate is below target',
                'message' => 'Published results are at ' . number_format($passSummary['rate'], 1) . '% for the selected period. Consider intervention on weak departments.',
                'actionLabel' => 'Open Exams',
                'actionHref' => route('admin.exams.index'),
            ];
        }

        if ($applicationVolume === 0) {
            $alerts[] = [
                'tone' => 'info',
                'title' => 'No new applications in this window',
                'message' => 'Admissions activity is quiet for the selected window. Review the apply funnel or promote the application CTA.',
                'actionLabel' => 'Open Apply Page',
                'actionHref' => route('public.apply'),
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'tone' => 'success',
                'title' => 'Operations are stable',
                'message' => 'No critical dashboard alerts were detected for the selected period.',
                'actionLabel' => 'View Reports',
                'actionHref' => route('admin.audit-logs.index'),
            ];
        }

        return array_slice($alerts, 0, 3);
    }

    private function buildDashboardState(array $payload, string $period, ?AcademicSession $selectedSession, Collection $sessionOptions, array $window): array
    {
        $selectedSessionId = $selectedSession?->id;

        return [
            'period' => $period,
            'periodLabel' => $window['label'],
            'sessionId' => $selectedSessionId,
            'sessionLabel' => $selectedSession?->name ?? 'Current session',
            'rangeLabel' => bsDate($window['start'], 'd M Y') . ' - ' . bsDate($window['end'], 'd M Y'),
            'updatedAt' => now()->toIso8601String(),
            'periodOptions' => [
                ['value' => 'week', 'label' => 'Week', 'hint' => 'Last 7 days'],
                ['value' => 'month', 'label' => 'Month', 'hint' => 'Last 30 days'],
                ['value' => 'session', 'label' => 'Session', 'hint' => 'Full academic session'],
            ],
            'sessionOptions' => $sessionOptions->map(function (AcademicSession $session) use ($selectedSessionId) {
                return [
                    'id' => $session->id,
                    'label' => trim($session->name . ($session->name_bs ? ' / ' . $session->name_bs : '')),
                    'selected' => $selectedSessionId === $session->id,
                ];
            })->values()->all(),
            'kpis' => collect($payload['kpiCards'])->map(function (array $card) {
                return [
                    'key' => $card['key'],
                    'title' => $card['title'],
                    'value' => $card['value'],
                    'suffix' => $card['suffix'],
                    'trend' => $card['trend'],
                    'trendDirection' => $card['trendDirection'],
                    'note' => $card['note'],
                    'tone' => $card['tone'],
                    'href' => $card['href'],
                ];
            })->values()->all(),
            'charts' => [
                'enrollment' => $payload['chartData']['enrollment'],
                'departmentPerformance' => [
                    'labels' => $payload['chartData']['departmentPerformance']['labels'],
                    'values' => $payload['chartData']['departmentPerformance']['values'],
                ],
            ],
            'alerts' => collect($payload['alerts'])->map(function (array $alert) {
                return [
                    'tone' => $alert['tone'],
                    'title' => $alert['title'],
                    'message' => $alert['message'],
                    'actionLabel' => $alert['actionLabel'],
                    'actionHref' => $alert['actionHref'],
                ];
            })->values()->all(),
            'highlight' => $payload['highlight'] ? [
                'name' => $payload['highlight']['name'],
                'label' => $payload['highlight']['label'],
                'score' => $payload['highlight']['score'],
                'students' => $payload['highlight']['students'],
                'attendance_rate' => $payload['highlight']['attendance_rate'],
                'pass_rate' => $payload['highlight']['pass_rate'],
                'summary' => $payload['highlight']['summary'],
            ] : null,
            'recentNotices' => $payload['recentNotices']->map(function (Notice $notice) {
                return [
                    'title' => $notice->title,
                    'excerpt' => Str::limit(strip_tags((string) $notice->content), 120),
                    'date' => bsDate($notice->created_at, 'd M Y'),
                    'author' => $notice->author->name ?? 'System',
                    'type' => $notice->type,
                    'href' => route('admin.notices.edit', $notice),
                ];
            })->values()->all(),
            'recentApplications' => $payload['recentApplications']->map(function (Application $application) {
                $status = $application->status ?? 'pending';

                return [
                    'full_name' => $application->full_name,
                    'department' => $application->department->name ?? 'General intake',
                    'phone' => $application->phone,
                    'email' => $application->email,
                    'date' => bsDate($application->created_at, 'd M Y'),
                    'status' => $status,
                    'statusLabel' => ucfirst($status),
                    'statusClass' => match ($status) {
                        'reviewed' => 'bg-sky-100 text-sky-800 ring-1 ring-sky-200',
                        'contacted' => 'bg-violet-100 text-violet-800 ring-1 ring-violet-200',
                        'accepted' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
                        'rejected' => 'bg-rose-100 text-rose-800 ring-1 ring-rose-200',
                        default => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200',
                    },
                    'href' => route('admin.applications.show', $application),
                ];
            })->values()->all(),
            'ctevtNotices' => [
                'general' => $payload['ctevtGeneralNotices'],
                'result' => $payload['ctevtResultNotices'],
            ],
        ];
    }

    private function buildHighlight(?array $topDepartment, int $students, int $admissions, int $applications, float $passRate, float $attendanceRate): ?array
    {
        if (!$topDepartment || empty($topDepartment['has_data'])) {
            return null;
        }

        return [
            'name' => $topDepartment['name'],
            'label' => $topDepartment['label'],
            'score' => $topDepartment['score'],
            'students' => $topDepartment['students'],
            'attendance_rate' => $topDepartment['attendance_rate'],
            'pass_rate' => $topDepartment['pass_rate'],
            'summary' => 'Balanced academic performance across attendance and results makes this the strongest department in the current view.',
            'context' => [
                'students' => number_format($students),
                'admissions' => number_format($admissions),
                'applications' => number_format($applications),
                'pass_rate' => number_format($passRate, 1) . '%',
                'attendance_rate' => number_format($attendanceRate, 1) . '%',
            ],
        ];
    }

    private function makeBuckets(Carbon $start, Carbon $end, string $bucketType): array
    {
        $cursor = $bucketType === 'month'
            ? $start->copy()->startOfMonth()
            : $start->copy()->startOfDay();

        $buckets = [];

        while ($cursor <= $end) {
            $key = $bucketType === 'month' ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            $label = $bucketType === 'month' ? $cursor->format('M Y') : $cursor->format('d M');

            $buckets[$key] = [
                'label' => $label,
                'value' => 0,
            ];

            $cursor = $bucketType === 'month'
                ? $cursor->copy()->addMonthNoOverflow()->startOfMonth()
                : $cursor->copy()->addDay();
        }

        return $buckets;
    }

    private function formatTrend(float|int $current, float|int $previous): array
    {
        if ($previous <= 0) {
            return [
                'text' => $current > 0 ? '+100.0%' : '0.0%',
                'direction' => $current > 0 ? 'up' : 'flat',
            ];
        }

        $delta = (($current - $previous) / $previous) * 100;

        return [
            'text' => ($delta > 0 ? '+' : '') . number_format(abs($delta), 1) . '%',
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
        ];
    }

    private function greeting(): string
    {
        $hour = Carbon::now()->hour;

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    private function greetingWindowLabel(): string
    {
        $hour = Carbon::now()->hour;

        return match (true) {
            $hour < 12 => 'this morning',
            $hour < 17 => 'this afternoon',
            default => 'tonight',
        };
    }
}
