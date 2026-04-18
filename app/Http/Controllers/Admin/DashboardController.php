<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AcademicSessionSemester;
use App\Models\Alumni;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Teacher;
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

        $cacheKey = sprintf('admin_dashboard_v2:%s:%s', $period, $period === 'session' ? ($selectedSession?->id ?? 'none') : 'default');

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
            $totalTeachers = Teacher::active()->count();
            $totalParents = ParentModel::count();
            $totalAlumni = Alumni::count();

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

            // Semester status panel
            $runningSemesters = $this->buildSemesterStatus($activeSession);

            // Department performance index (avg composite score)
            $deptIndex = $departmentPerformance['rows']->where('has_data', true)->avg('score');

            $recentNotices = Notice::published()
                ->with(['author', 'department'])
                ->latest()
                ->take(4)
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
                $currentApplicationsVolume,
                $currentAdmissions,
                $previousAdmissions,
            );

            $highlight = $this->buildHighlight(
                $departmentPerformance['top'],
                $currentStudents,
                $totalTeachers,
                $totalParents,
                $totalAlumni,
            );

            $kpiCards = [
                [
                    'key' => 'students',
                    'title' => 'Total Students',
                    'value' => number_format($currentStudents),
                    'suffix' => null,
                    'trend' => $currentAdmissionsTrend['text'],
                    'trendDirection' => $currentAdmissionsTrend['direction'],
                    'note' => number_format($currentAdmissions) . ' new in ' . $window['label'],
                    'icon' => 'students',
                    'tone' => 'blue',
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
                    'tone' => 'emerald',
                    'href' => '#',
                ],
                [
                    'key' => 'pass',
                    'title' => 'Pass Rate',
                    'value' => number_format($passSummary['rate'], 1),
                    'suffix' => '%',
                    'trend' => $passTrend['text'],
                    'trendDirection' => $passTrend['direction'],
                    'note' => number_format($passSummary['passed']) . ' / ' . number_format($passSummary['total']) . ' marks',
                    'icon' => 'results',
                    'tone' => 'violet',
                    'href' => '#',
                ],
                [
                    'key' => 'applications',
                    'title' => 'Active Applications',
                    'value' => number_format($pendingApplications),
                    'suffix' => null,
                    'trend' => $applicationTrend['text'],
                    'trendDirection' => $applicationTrend['direction'],
                    'note' => number_format($currentApplicationsVolume) . ' in ' . $window['label'],
                    'icon' => 'applications',
                    'tone' => 'amber',
                    'href' => route('admin.applications.index'),
                ],
                [
                    'key' => 'semesters',
                    'title' => 'Running Semesters',
                    'value' => (string) count(array_filter($runningSemesters, fn ($s) => $s['status'] !== 'completed')),
                    'suffix' => null,
                    'trend' => count($runningSemesters) . ' total',
                    'trendDirection' => 'flat',
                    'note' => ($activeSession?->name ?? 'No session') . ' active',
                    'icon' => 'semesters',
                    'tone' => 'indigo',
                    'href' => route('admin.academic-sessions.index'),
                ],
                [
                    'key' => 'departments',
                    'title' => 'Dept. Performance',
                    'value' => $deptIndex !== null ? number_format($deptIndex, 1) : '—',
                    'suffix' => $deptIndex !== null ? '%' : null,
                    'trend' => $departmentPerformance['rows']->count() . ' departments',
                    'trendDirection' => 'flat',
                    'note' => 'Composite score avg',
                    'icon' => 'departments',
                    'tone' => 'rose',
                    'href' => route('admin.departments.index'),
                ],
            ];

            return [
                'kpiCards' => $kpiCards,
                'chartData' => [
                    'enrollment' => $enrollmentTrend,
                    'departmentPerformance' => $departmentPerformance,
                ],
                'runningSemesters' => $runningSemesters,
                'alerts' => $alerts,
                'highlight' => $highlight,
                'recentNotices' => $recentNotices,
                'recentApplications' => $recentApplications,
                'ctevtGeneralNotices' => $ctevtGeneralNotices,
                'ctevtResultNotices' => $ctevtResultNotices,
                'currentStudents' => $currentStudents,
                'totalTeachers' => $totalTeachers,
                'totalParents' => $totalParents,
                'totalAlumni' => $totalAlumni,
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

        return view('admin.dashboard-modern', array_merge($payload, [
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

    // ─── Semester Status Builder ───────────────────────────────

    private function buildSemesterStatus(?AcademicSession $session): array
    {
        if (!$session) {
            return [];
        }

        $semesters = AcademicSessionSemester::where('academic_session_id', $session->id)
            ->orderBy('semester_number')
            ->get();

        return $semesters->map(function (AcademicSessionSemester $sem) {
            $progress = 0;
            if ($sem->start_date && $sem->end_date) {
                $total = max($sem->start_date->diffInDays($sem->end_date), 1);
                $elapsed = max(now()->diffInDays($sem->start_date, false), 0);
                $progress = min(round(($elapsed / $total) * 100), 100);
                if ($sem->status === 'completed') {
                    $progress = 100;
                }
            }

            $delayLabels = [
                'exam_late' => 'Exam Delayed',
                'holidays' => 'Extended Holidays',
                'internal_delay' => 'Internal Delay',
                'admin_decision' => 'Admin Decision',
            ];

            return [
                'number' => $sem->semester_number,
                'label' => 'Semester ' . $sem->semester_number,
                'status' => $sem->status,
                'statusLabel' => match ($sem->status) {
                    'running' => 'Active',
                    'delayed' => 'Delayed',
                    'completed' => 'Completed',
                    default => ucfirst($sem->status),
                },
                'delayReason' => $sem->delay_reason ? ($delayLabels[$sem->delay_reason] ?? ucfirst($sem->delay_reason)) : null,
                'progress' => $progress,
                'startDate' => $sem->start_date ? bsDate($sem->start_date, 'd M Y') : null,
                'endDate' => $sem->end_date ? bsDate($sem->end_date, 'd M Y') : null,
                'isActive' => $sem->is_active,
                'notes' => $sem->notes,
            ];
        })->all();
    }

    // ─── Resolution helpers ────────────────────────────────────

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
            return ['start' => $now->copy()->subDays(6)->startOfDay(), 'end' => $now->copy()->endOfDay(), 'label' => 'Last 7 days', 'bucket' => 'day'];
        }

        if ($period === 'session') {
            $start = $session?->start_date ? Carbon::parse($session->start_date)->startOfDay() : $now->copy()->subDays(29)->startOfDay();
            $end = $session?->end_date ? Carbon::parse($session->end_date)->endOfDay() : $now->copy()->endOfDay();
            return ['start' => $start, 'end' => $end, 'label' => $session?->name ?? 'Selected session', 'bucket' => 'month'];
        }

        return ['start' => $now->copy()->subDays(29)->startOfDay(), 'end' => $now->copy()->endOfDay(), 'label' => 'Last 30 days', 'bucket' => 'day'];
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
                return ['start' => Carbon::parse($previousSession->start_date)->startOfDay(), 'end' => Carbon::parse($previousSession->end_date)->endOfDay(), 'session' => $previousSession];
            }
        }

        $days = max($start->diffInDays($end) + 1, 1);
        return ['start' => $start->copy()->subDays($days)->startOfDay(), 'end' => $start->copy()->subDay()->endOfDay(), 'session' => null];
    }

    // ─── Data loaders ──────────────────────────────────────────

    private function loadAttendanceRecords(Carbon $start, Carbon $end, ?AcademicSession $session): Collection
    {
        return Attendance::query()
            ->with(['student.department', 'attendanceSession'])
            ->when($session, function ($query) use ($session) {
                $query->whereHas('attendanceSession', fn ($q) => $q->where('academic_session_id', $session->id));
            }, function ($query) use ($start, $end) {
                $query->whereHas('attendanceSession', fn ($q) => $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]));
            })
            ->get();
    }

    private function loadPublishedMarks(Carbon $start, Carbon $end, ?AcademicSession $session): Collection
    {
        return Mark::query()
            ->published()
            ->with(['subject', 'student.department', 'exam'])
            ->when($session, function ($query) use ($session) {
                $query->whereHas('exam', fn ($q) => $q->where('academic_session_id', $session->id));
            }, function ($query) use ($start, $end) {
                $query->whereBetween('updated_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
            })
            ->get();
    }

    private function countAdmissions(Carbon $start, Carbon $end, ?AcademicSession $session): int
    {
        return Student::query()
            ->when($session, fn ($q) => $q->where('academic_session_id', $session->id), fn ($q) => $q->whereBetween('admission_date', [$start->toDateString(), $end->toDateString()]))
            ->whereNotNull('admission_date')
            ->count();
    }

    private function countApplications(Carbon $start, Carbon $end): int
    {
        return Application::query()
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();
    }

    // ─── Summarizers ───────────────────────────────────────────

    private function summarizeAttendance(Collection $records): array
    {
        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        return ['total' => $total, 'present' => $present, 'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0];
    }

    private function summarizeMarks(Collection $marks): array
    {
        $total = $marks->count();
        $passed = $marks->filter(fn ($mark) => $mark->is_passed)->count();
        return ['total' => $total, 'passed' => $passed, 'rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0.0];
    }

    // ─── Builders ──────────────────────────────────────────────

    private function buildEnrollmentTrend(Carbon $start, Carbon $end, string $period, ?AcademicSession $session): array
    {
        $bucketType = $period === 'session' ? 'month' : 'day';
        $buckets = $this->makeBuckets($start, $end, $bucketType);

        $students = Student::query()
            ->whereNotNull('admission_date')
            ->when($session, fn ($q) => $q->where('academic_session_id', $session->id), fn ($q) => $q->whereBetween('admission_date', [$start->toDateString(), $end->toDateString()]))
            ->get(['admission_date']);

        foreach ($students as $student) {
            $admissionDate = Carbon::parse($student->admission_date);
            $bucketKey = $bucketType === 'month' ? $admissionDate->format('Y-m') : $admissionDate->format('Y-m-d');
            if (isset($buckets[$bucketKey])) {
                $buckets[$bucketKey]['value']++;
            }
        }

        return [
            'labels' => array_values(array_map(fn (array $b) => $b['label'], $buckets)),
            'values' => array_values(array_map(fn (array $b) => $b['value'], $buckets)),
        ];
    }

    private function buildDepartmentPerformance(Collection $attendanceRecords, Collection $marks): array
    {
        $departments = Department::active()->withCount('students')->get();

        $rows = $departments->map(function (Department $department) use ($attendanceRecords, $marks) {
            $deptAttendance = $attendanceRecords->filter(fn ($r) => (int) data_get($r, 'student.department_id') === (int) $department->id);
            $deptMarks = $marks->filter(fn ($m) => (int) data_get($m, 'student.department_id') === (int) $department->id);

            $attendanceRate = $deptAttendance->count() > 0 ? round(($deptAttendance->where('status', 'present')->count() / $deptAttendance->count()) * 100, 1) : null;
            $passRate = $deptMarks->count() > 0 ? round(($deptMarks->filter(fn ($m) => $m->is_passed)->count() / $deptMarks->count()) * 100, 1) : null;

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

    private function buildAlerts(Collection $departmentRows, array $attendanceSummary, array $passSummary, int $pendingApplications, int $applicationVolume, int $admissions, int $previousAdmissions): array
    {
        $alerts = [];

        $lowAttendance = $departmentRows->first(fn (array $r) => $r['attendance_rate'] !== null && $r['attendance_rate'] < 75);
        if ($lowAttendance) {
            $alerts[] = [
                'tone' => 'danger',
                'icon' => 'alert-triangle',
                'title' => 'Attendance dropped in ' . $lowAttendance['name'],
                'message' => 'Attendance is ' . number_format($lowAttendance['attendance_rate'], 1) . '% — below the 75% threshold.',
                'actionLabel' => 'View Details',
                'actionHref' => route('admin.students.index'),
            ];
        }

        if ($pendingApplications >= 10) {
            $alerts[] = [
                'tone' => 'warning',
                'icon' => 'clock',
                'title' => number_format($pendingApplications) . ' applications awaiting review',
                'message' => 'Admissions queue is growing. Clear the backlog to avoid delays.',
                'actionLabel' => 'Review',
                'actionHref' => route('admin.applications.index'),
            ];
        }

        if ($passSummary['total'] > 0 && $passSummary['rate'] < 70) {
            $alerts[] = [
                'tone' => 'warning',
                'icon' => 'trending-down',
                'title' => 'Pass rate below target',
                'message' => 'Published results at ' . number_format($passSummary['rate'], 1) . '%. Consider intervention.',
                'actionLabel' => 'Open Exams',
                'actionHref' => route('admin.exams.index'),
            ];
        }

        if ($admissions > 0 && $previousAdmissions > 0) {
            $changePercent = round((($admissions - $previousAdmissions) / $previousAdmissions) * 100, 1);
            if ($changePercent > 15) {
                $alerts[] = [
                    'tone' => 'success',
                    'icon' => 'trending-up',
                    'title' => 'Admissions increased by ' . number_format(abs($changePercent), 1) . '%',
                    'message' => 'Great momentum — enrollment trending upward.',
                    'actionLabel' => 'View Students',
                    'actionHref' => route('admin.students.index'),
                ];
            }
        }

        $topDept = $departmentRows->first(fn (array $r) => $r['has_data'] && $r['score'] > 80);
        if ($topDept && count($alerts) < 4) {
            $alerts[] = [
                'tone' => 'success',
                'icon' => 'award',
                'title' => $topDept['name'] . ' leads in performance',
                'message' => 'Composite score of ' . number_format($topDept['score'], 1) . '% this period.',
                'actionLabel' => 'View Dept',
                'actionHref' => route('admin.departments.show', $topDept['id']),
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'tone' => 'success',
                'icon' => 'shield-check',
                'title' => 'Operations are stable',
                'message' => 'No critical alerts detected for the selected period.',
                'actionLabel' => 'View Reports',
                'actionHref' => route('admin.audit-logs.index'),
            ];
        }

        return array_slice($alerts, 0, 4);
    }

    private function buildHighlight(?array $topDepartment, int $students, int $teachers, int $parents, int $alumni): ?array
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
            'summary' => 'Balanced academic performance across attendance and results makes this the strongest department.',
            'quickStats' => [
                'teachers' => $teachers,
                'parents' => $parents,
                'alumni' => $alumni,
            ],
        ];
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
            'sessionOptions' => $sessionOptions->map(fn (AcademicSession $s) => [
                'id' => $s->id,
                'label' => trim($s->name . ($s->name_bs ? ' / ' . $s->name_bs : '')),
                'selected' => $selectedSessionId === $s->id,
            ])->values()->all(),
            'kpis' => collect($payload['kpiCards'])->map(fn (array $c) => [
                'key' => $c['key'], 'title' => $c['title'], 'value' => $c['value'], 'suffix' => $c['suffix'],
                'trend' => $c['trend'], 'trendDirection' => $c['trendDirection'], 'note' => $c['note'], 'tone' => $c['tone'],
            ])->values()->all(),
            'charts' => [
                'enrollment' => $payload['chartData']['enrollment'],
                'departmentPerformance' => [
                    'labels' => $payload['chartData']['departmentPerformance']['labels'],
                    'values' => $payload['chartData']['departmentPerformance']['values'],
                ],
            ],
            'runningSemesters' => $payload['runningSemesters'],
            'alerts' => collect($payload['alerts'])->map(fn (array $a) => [
                'tone' => $a['tone'], 'icon' => $a['icon'], 'title' => $a['title'],
                'message' => $a['message'], 'actionLabel' => $a['actionLabel'], 'actionHref' => $a['actionHref'],
            ])->values()->all(),
            'highlight' => $payload['highlight'] ? [
                'name' => $payload['highlight']['name'],
                'label' => $payload['highlight']['label'],
                'score' => $payload['highlight']['score'],
                'students' => $payload['highlight']['students'],
                'attendance_rate' => $payload['highlight']['attendance_rate'],
                'pass_rate' => $payload['highlight']['pass_rate'],
                'summary' => $payload['highlight']['summary'],
                'quickStats' => $payload['highlight']['quickStats'],
            ] : null,
            'recentNotices' => $payload['recentNotices']->map(fn (Notice $n) => [
                'title' => $n->title,
                'excerpt' => Str::limit(strip_tags((string) $n->content), 100),
                'date' => bsDate($n->created_at, 'd M Y'),
                'author' => $n->author->name ?? 'System',
                'type' => $n->type,
                'href' => route('admin.notices.edit', $n),
            ])->values()->all(),
            'recentApplications' => $payload['recentApplications']->map(function (Application $app) {
                $status = $app->status ?? 'pending';
                return [
                    'full_name' => $app->full_name,
                    'department' => $app->department->name ?? 'General intake',
                    'phone' => $app->phone,
                    'email' => $app->email,
                    'date' => bsDate($app->created_at, 'd M Y'),
                    'status' => $status,
                    'statusLabel' => ucfirst($status),
                    'statusClass' => match ($status) {
                        'reviewed' => 'bg-sky-100 text-sky-700',
                        'contacted' => 'bg-violet-100 text-violet-700',
                        'accepted' => 'bg-emerald-100 text-emerald-700',
                        'rejected' => 'bg-rose-100 text-rose-700',
                        default => 'bg-amber-100 text-amber-700',
                    },
                    'href' => route('admin.applications.show', $app),
                ];
            })->values()->all(),
            'ctevtNotices' => [
                'general' => $payload['ctevtGeneralNotices'],
                'result' => $payload['ctevtResultNotices'],
            ],
        ];
    }

    // ─── Utility ───────────────────────────────────────────────

    private function makeBuckets(Carbon $start, Carbon $end, string $bucketType): array
    {
        $cursor = $bucketType === 'month' ? $start->copy()->startOfMonth() : $start->copy()->startOfDay();
        $buckets = [];

        while ($cursor <= $end) {
            $key = $bucketType === 'month' ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            $label = $bucketType === 'month' ? $cursor->format('M Y') : $cursor->format('d M');
            $buckets[$key] = ['label' => $label, 'value' => 0];
            $cursor = $bucketType === 'month' ? $cursor->copy()->addMonthNoOverflow()->startOfMonth() : $cursor->copy()->addDay();
        }

        return $buckets;
    }

    private function formatTrend(float|int $current, float|int $previous): array
    {
        if ($previous <= 0) {
            return ['text' => $current > 0 ? '+100.0%' : '0.0%', 'direction' => $current > 0 ? 'up' : 'flat'];
        }

        $delta = (($current - $previous) / $previous) * 100;
        return ['text' => ($delta > 0 ? '+' : '') . number_format(abs($delta), 1) . '%', 'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat')];
    }

    private function greeting(): string
    {
        $hour = Carbon::now()->hour;
        return match (true) { $hour < 12 => 'Good morning', $hour < 17 => 'Good afternoon', default => 'Good evening' };
    }
}
