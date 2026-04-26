<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AcademicSessionSemester;
use App\Models\Alumni;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\PublicDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            $currentScopeSession = $period === 'session' ? $selectedSession : null;
            $currentAttendanceSummary = $this->attendanceSummaryForWindow($window['start'], $window['end'], $currentScopeSession);
            $previousAttendanceSummary = $this->attendanceSummaryForWindow($comparison['start'], $comparison['end'], $comparison['session']);

            $passSummary = $this->marksSummaryForWindow($window['start'], $window['end'], $currentScopeSession);
            $previousPassSummary = $this->marksSummaryForWindow($comparison['start'], $comparison['end'], $comparison['session']);

            $currentAdmissions = $this->countAdmissions($window['start'], $window['end'], $period === 'session' ? $selectedSession : null);
            $previousAdmissions = $this->countAdmissions($comparison['start'], $comparison['end'], $comparison['session']);

            $currentStudents = Student::active()->count();
            $totalTeachers = Teacher::active()->count();
            $totalParents = ParentModel::count();
            $totalAlumni = Alumni::count();

            $attendanceSummary = $currentAttendanceSummary;
            $departmentPerformance = $this->buildDepartmentPerformance($window['start'], $window['end'], $currentScopeSession);
            $enrollmentTrend = $this->buildEnrollmentTrend($window['start'], $window['end'], $period, $selectedSession);

            $currentAdmissionsTrend = $this->formatTrend($currentAdmissions, $previousAdmissions);
            $attendanceTrend = $this->formatTrend($attendanceSummary['rate'], $previousAttendanceSummary['rate']);
            $passTrend = $this->formatTrend($passSummary['rate'], $previousPassSummary['rate']);

            // Semester status panel
            $runningSemesters = $this->buildSemesterStatus($activeSession);

            // Department performance index (avg composite score)
            $deptIndex = $departmentPerformance['rows']->where('has_data', true)->avg('score');

            $recentNotices = Notice::published()
                ->whereIn('type', ['general', 'department', 'teachers', 'exam'])
                ->with(['author', 'department'])
                ->latest()
                ->take(4)
                ->get();

            // Attendance chart data with real Nepali dates
            $attendanceChartData = $this->buildAttendanceChartData();

            // Grade distribution from active students' marks
            $gradeDistribution = $this->buildGradeDistribution($window['start'], $window['end'], $currentScopeSession);

            $alerts = $this->buildAlerts(
                $departmentPerformance['rows'],
                $attendanceSummary,
                $passSummary,
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
                    'key' => 'teachers',
                    'title' => 'Total Teachers',
                    'value' => number_format($totalTeachers),
                    'suffix' => null,
                    'trend' => 'Active staff',
                    'trendDirection' => 'flat',
                    'note' => 'Teaching faculty',
                    'icon' => 'teachers',
                    'tone' => 'indigo',
                    'href' => route('admin.teachers.index'),
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
                    'href' => route('admin.attendance.index'),
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
                    'href' => route('admin.exams.index'),
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
                'attendanceChartData' => $attendanceChartData,
                'gradeDistribution' => $gradeDistribution,
                'currentStudents' => $currentStudents,
                'totalTeachers' => $totalTeachers,
                'totalParents' => $totalParents,
                'totalAlumni' => $totalAlumni,
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
                'startDate' => $sem->start_date ? bsDate($sem->start_date, 'Y, F d') : null,
                'endDate' => $sem->end_date ? bsDate($sem->end_date, 'Y, F d') : null,
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

    private function attendanceSummaryForWindow(Carbon $start, Carbon $end, ?AcademicSession $session): array
    {
        $row = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->when(
                $session,
                fn ($query) => $query->where('attendance_sessions.academic_session_id', $session->id),
                fn ($query) => $query->whereBetween('attendance_sessions.date', [$start->toDateString(), $end->toDateString()])
            )
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
            ->first();

        $total = (int) ($row->total ?? 0);
        $present = (int) ($row->present ?? 0);

        return [
            'total' => $total,
            'present' => $present,
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
        ];
    }

    private function marksSummaryForWindow(Carbon $start, Carbon $end, ?AcademicSession $session): array
    {
        $rows = Mark::query()
            ->published()
            ->join('subjects', 'subjects.id', '=', 'marks.subject_id')
            ->join('exams', 'exams.id', '=', 'marks.exam_id')
            ->when(
                $session,
                fn ($query) => $query->where('exams.academic_session_id', $session->id),
                fn ($query) => $query->whereBetween('marks.updated_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            )
            ->select([
                'marks.is_absent',
                'marks.is_withheld',
                'marks.internal_theory_marks',
                'marks.external_theory_marks',
                'marks.internal_practical_marks',
                'marks.external_practical_marks',
                'subjects.type as subject_type',
                'subjects.pass_marks_internal_theory',
                'subjects.pass_marks_external_theory',
                'subjects.pass_marks_internal_practical',
                'subjects.pass_marks_external_practical',
            ])
            ->cursor();

        $total = 0;
        $passed = 0;

        foreach ($rows as $row) {
            $total++;
            if ($this->markRowIsPassed($row)) {
                $passed++;
            }
        }

        return [
            'total' => $total,
            'passed' => $passed,
            'rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0.0,
        ];
    }

    private function countAdmissions(Carbon $start, Carbon $end, ?AcademicSession $session): int
    {
        return Student::query()
            ->when($session, fn ($q) => $q->where('academic_session_id', $session->id), fn ($q) => $q->whereBetween('admission_date', [$start->toDateString(), $end->toDateString()]))
            ->whereNotNull('admission_date')
            ->count();
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

    private function buildDepartmentPerformance(Carbon $start, Carbon $end, ?AcademicSession $session): array
    {
        $departments = Department::active()->withCount('students')->get();

        $attendanceByDepartment = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->when(
                $session,
                fn ($query) => $query->where('attendance_sessions.academic_session_id', $session->id),
                fn ($query) => $query->whereBetween('attendance_sessions.date', [$start->toDateString(), $end->toDateString()])
            )
            ->selectRaw('students.department_id as department_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
            ->groupBy('students.department_id')
            ->get()
            ->keyBy('department_id');

        $marksByDepartment = $this->markStatsByDepartment($start, $end, $session);

        $rows = $departments->map(function (Department $department) use ($attendanceByDepartment, $marksByDepartment) {
            $attendanceRow = $attendanceByDepartment->get($department->id);
            $attendanceTotal = (int) ($attendanceRow->total ?? 0);
            $attendancePresent = (int) ($attendanceRow->present ?? 0);
            $attendanceRate = $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : null;

            $markRow = $marksByDepartment[$department->id] ?? ['total' => 0, 'passed' => 0];
            $markTotal = (int) ($markRow['total'] ?? 0);
            $markPassed = (int) ($markRow['passed'] ?? 0);
            $passRate = $markTotal > 0 ? round(($markPassed / $markTotal) * 100, 1) : null;

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
                'has_data' => $attendanceTotal > 0 || $markTotal > 0,
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

    private function markStatsByDepartment(Carbon $start, Carbon $end, ?AcademicSession $session): array
    {
        $rows = Mark::query()
            ->published()
            ->join('subjects', 'subjects.id', '=', 'marks.subject_id')
            ->join('students', 'students.id', '=', 'marks.student_id')
            ->join('exams', 'exams.id', '=', 'marks.exam_id')
            ->when(
                $session,
                fn ($query) => $query->where('exams.academic_session_id', $session->id),
                fn ($query) => $query->whereBetween('marks.updated_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            )
            ->select([
                'students.department_id as department_id',
                'marks.is_absent',
                'marks.is_withheld',
                'marks.internal_theory_marks',
                'marks.external_theory_marks',
                'marks.internal_practical_marks',
                'marks.external_practical_marks',
                'subjects.type as subject_type',
                'subjects.pass_marks_internal_theory',
                'subjects.pass_marks_external_theory',
                'subjects.pass_marks_internal_practical',
                'subjects.pass_marks_external_practical',
            ])
            ->cursor();

        $stats = [];
        foreach ($rows as $row) {
            $departmentId = (int) ($row->department_id ?? 0);
            if (! isset($stats[$departmentId])) {
                $stats[$departmentId] = ['total' => 0, 'passed' => 0];
            }

            $stats[$departmentId]['total']++;
            if ($this->markRowIsPassed($row)) {
                $stats[$departmentId]['passed']++;
            }
        }

        return $stats;
    }

    private function markRowIsPassed(object $row): bool
    {
        if ((bool) $row->is_absent || (bool) $row->is_withheld) {
            return false;
        }

        $internalTheory = (float) ($row->internal_theory_marks ?? 0);
        $externalTheory = (float) ($row->external_theory_marks ?? 0);
        $internalPractical = (float) ($row->internal_practical_marks ?? 0);
        $externalPractical = (float) ($row->external_practical_marks ?? 0);

        $theoryPass = $internalTheory >= (float) ($row->pass_marks_internal_theory ?? 0)
            && $externalTheory >= (float) ($row->pass_marks_external_theory ?? 0);

        if (! $theoryPass) {
            return false;
        }

        $requiresPractical = in_array((string) ($row->subject_type ?? 'theory'), ['practical', 'both'], true);
        if (! $requiresPractical) {
            return true;
        }

        return $internalPractical >= (float) ($row->pass_marks_internal_practical ?? 0)
            && $externalPractical >= (float) ($row->pass_marks_external_practical ?? 0);
    }

    private function buildAlerts(Collection $departmentRows, array $attendanceSummary, array $passSummary, int $admissions, int $previousAdmissions): array
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
            'rangeLabel' => bsDate($window['start'], 'Y, F d') . ' - ' . bsDate($window['end'], 'Y, F d'),
            'updatedAt' => bsDate(now(), 'Y, F d') . ', ' . now()->format('h:i A'),
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
                'date' => bsDate($n->created_at, 'Y, F d'),
                'author' => $n->author->name ?? 'System',
                'type' => $n->type,
                'href' => route('admin.notices.edit', $n),
            ])->values()->all(),
        ];
    }

    // ─── Utility ───────────────────────────────────────────────

    private function makeBuckets(Carbon $start, Carbon $end, string $bucketType): array
    {
        $cursor = $bucketType === 'month' ? $start->copy()->startOfMonth() : $start->copy()->startOfDay();
        $buckets = [];

        while ($cursor <= $end) {
            $key = $bucketType === 'month' ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            $label = $bucketType === 'month' ? bsDate($cursor, 'F Y') : bsDate($cursor, 'd F');
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

    private function buildAttendanceChartData(): array
    {
        $today = Carbon::now();
        
        // 7 days data - last 7 days with real attendance
        $sevenDaysLabels = [];
        $sevenDaysData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $sevenDaysLabels[] = bsDate($date, 'F d'); // e.g., "Baisakh 15"
            
            // Get real attendance data for this date
            $attendanceRow = DB::table('attendances')
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->where('attendance_sessions.date', $date->toDateString())
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();
            
            $total = (int) ($attendanceRow->total ?? 0);
            $present = (int) ($attendanceRow->present ?? 0);
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            
            $sevenDaysData[] = $rate;
        }
        
        // 30 days data - last 30 days with full date labels (month and day)
        $thirtyDaysLabels = [];
        $thirtyDaysData = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $thirtyDaysLabels[] = bsDate($date, 'F d'); // e.g., "Baisakh 15", "Baisakh 16", etc.
            
            // Get real attendance data for this date
            $attendanceRow = DB::table('attendances')
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->where('attendance_sessions.date', $date->toDateString())
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();
            
            $total = (int) ($attendanceRow->total ?? 0);
            $present = (int) ($attendanceRow->present ?? 0);
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            
            $thirtyDaysData[] = $rate;
        }
        
        return [
            '7' => [
                'labels' => $sevenDaysLabels,
                'data' => $sevenDaysData,
            ],
            '30' => [
                'labels' => $thirtyDaysLabels,
                'data' => $thirtyDaysData,
            ],
        ];
    }

    private function buildGradeDistribution(Carbon $start, Carbon $end, ?AcademicSession $session): array
    {
        // Get marks for active students only
        $marks = Mark::query()
            ->published()
            ->join('students', 'students.id', '=', 'marks.student_id')
            ->join('subjects', 'subjects.id', '=', 'marks.subject_id')
            ->join('exams', 'exams.id', '=', 'marks.exam_id')
            ->where('students.status', 'active') // Only active students
            ->where('students.is_archived', false)
            ->when(
                $session,
                fn ($query) => $query->where('exams.academic_session_id', $session->id),
                fn ($query) => $query->whereBetween('marks.updated_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            )
            ->select([
                'marks.internal_theory_marks',
                'marks.external_theory_marks',
                'marks.internal_practical_marks',
                'marks.external_practical_marks',
                'marks.is_absent',
                'marks.is_withheld',
                'subjects.type as subject_type',
                'subjects.full_marks_internal_theory',
                'subjects.full_marks_external_theory',
                'subjects.full_marks_internal_practical',
                'subjects.full_marks_external_practical',
            ])
            ->get();

        $gradeCounts = [
            'A+' => 0,  // 90-100%
            'A' => 0,   // 80-89%
            'B+' => 0,  // 70-79%
            'B' => 0,   // 60-69%
            'C' => 0,   // 50-59%
            'F' => 0,   // <50%
        ];

        $total = 0;

        foreach ($marks as $mark) {
            // Skip absent or withheld
            if ($mark->is_absent || $mark->is_withheld) {
                continue;
            }

            // Calculate total marks
            $obtainedMarks = ($mark->internal_theory_marks ?? 0) + ($mark->external_theory_marks ?? 0);
            $fullMarks = ($mark->full_marks_internal_theory ?? 0) + ($mark->full_marks_external_theory ?? 0);

            // Add practical if applicable
            if (in_array($mark->subject_type, ['practical', 'both'])) {
                $obtainedMarks += ($mark->internal_practical_marks ?? 0) + ($mark->external_practical_marks ?? 0);
                $fullMarks += ($mark->full_marks_internal_practical ?? 0) + ($mark->full_marks_external_practical ?? 0);
            }

            if ($fullMarks <= 0) {
                continue;
            }

            $percentage = ($obtainedMarks / $fullMarks) * 100;
            $total++;

            // Categorize by grade
            if ($percentage >= 90) {
                $gradeCounts['A+']++;
            } elseif ($percentage >= 80) {
                $gradeCounts['A']++;
            } elseif ($percentage >= 70) {
                $gradeCounts['B+']++;
            } elseif ($percentage >= 60) {
                $gradeCounts['B']++;
            } elseif ($percentage >= 50) {
                $gradeCounts['C']++;
            } else {
                $gradeCounts['F']++;
            }
        }

        // Convert to percentages
        $gradePercentages = [];
        foreach ($gradeCounts as $grade => $count) {
            $gradePercentages[$grade] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return [
            'labels' => ['A+ (90-100)', 'A (80-89)', 'B+ (70-79)', 'B (60-69)', 'C (50-59)', 'F (<50)'],
            'data' => [
                $gradePercentages['A+'],
                $gradePercentages['A'],
                $gradePercentages['B+'],
                $gradePercentages['B'],
                $gradePercentages['C'],
                $gradePercentages['F'],
            ],
            'counts' => [
                $gradeCounts['A+'],
                $gradeCounts['A'],
                $gradeCounts['B+'],
                $gradeCounts['B'],
                $gradeCounts['C'],
                $gradeCounts['F'],
            ],
            'total' => $total,
            'hasData' => $total > 0,
        ];
    }
}
