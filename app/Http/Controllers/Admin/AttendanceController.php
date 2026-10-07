<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $window = $filters['window'];
        $trendWindow = [
            'start' => now()->copy()->subDays(29)->startOfDay(),
            'end' => now()->copy()->endOfDay(),
        ];
        $weekWindow = [
            'start' => now()->copy()->subDays(6)->startOfDay(),
            'end' => now()->copy()->endOfDay(),
        ];
        $previousWeekWindow = [
            'start' => now()->copy()->subDays(13)->startOfDay(),
            'end' => now()->copy()->subDays(7)->endOfDay(),
        ];
        $todayWindow = [
            'start' => now()->copy()->startOfDay(),
            'end' => now()->copy()->endOfDay(),
        ];

        $sessionQuery = $this->baseSessionQuery($filters, $window['start'], $window['end']);
        $attendanceSessions = (clone $sessionQuery)
            ->paginate(12, ['attendance_sessions.*'], 'page')
            ->withQueryString();
        $sessionRows = (clone $sessionQuery)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $recordsWindow = $this->baseRecordQuery($filters, $window['start'], $window['end'])->get();
        $recordsTrend = $this->baseRecordQuery($filters, $trendWindow['start'], $trendWindow['end'])->get();
        $recordsToday = $this->baseRecordQuery($filters, $todayWindow['start'], $todayWindow['end'])->get();
        $recordsYesterday = $this->baseRecordQuery($filters, now()->copy()->subDay()->startOfDay(), now()->copy()->subDay()->endOfDay())->get();
        $recordsWeek = $this->baseRecordQuery($filters, $weekWindow['start'], $weekWindow['end'])->get();
        $recordsPreviousWeek = $this->baseRecordQuery($filters, $previousWeekWindow['start'], $previousWeekWindow['end'])->get();
        $sessionWeekRows = (clone $this->baseSessionQuery($filters, $weekWindow['start'], $weekWindow['end']))->orderBy('date')->get();
        $sessionPreviousWeekRows = (clone $this->baseSessionQuery($filters, $previousWeekWindow['start'], $previousWeekWindow['end']))->orderBy('date')->get();

        $studentPaginator = $this->baseStudentQuery($filters)
            ->orderBy('roll_number')
            ->orderBy('student_no')
            ->paginate(12, ['*'], 'student_page')
            ->withQueryString();
        $studentRows = $this->decorateStudentRows(
            $studentPaginator->getCollection(),
            $filters,
            $window['start'],
            $window['end']
        );
        $studentPaginator->setCollection($studentRows);

        $teacherRows = $this->buildTeacherRows($sessionRows);
        $charts = $this->buildCharts($sessionRows, $recordsWindow, $recordsTrend, $window['start'], $window['end']);
        $kpis = $this->buildKpis(
            $sessionRows,
            $recordsToday,
            $recordsYesterday,
            $recordsWeek,
            $recordsPreviousWeek,
            $sessionWeekRows,
            $sessionPreviousWeekRows
        );
        $rules = $this->buildRules($sessionRows, $recordsWindow, $studentRows, $teacherRows);

        return view('admin.attendance.index', [
            'selectedSession' => $filters['selectedSession'],
            'window' => $window,
            'filters' => $filters,
            'attendanceSessions' => $attendanceSessions,
            'studentRows' => $studentPaginator,
            'teacherRows' => $teacherRows,
            'charts' => $charts,
            'kpis' => $kpis,
            'rules' => $rules,
            'departments' => Department::active()->orderBy('name')->get(['id', 'name', 'code']),
            'programs' => Program::active()->with('department:id,name,code')->orderBy('name')->get(['id', 'department_id', 'name', 'code']),
            'subjects' => Subject::query()->with('program:id,name,code,department_id')->orderBy('semester')->orderBy('name')->get(['id', 'program_id', 'semester', 'name', 'code']),
            'teachers' => Teacher::active()->with('user:id,name,avatar')->orderBy('id')->get(['id', 'user_id', 'department_id', 'designation']),
            'rangeOptions' => $this->rangeOptions(),
            'runningSemesters' => $sessionRows->pluck('semester')->filter()->unique()->sort()->values(),
        ]);
    }

    public function session(AttendanceSession $attendanceSession)
    {
        $attendanceSession->load([
            'academicSession:id,name,name_bs',
            'teacher.user:id,name,avatar',
            'teacher.department:id,name,code',
            'subject:id,name,code,type,semester,program_id,credit_hours',
            'program.department:id,name,code',
        ]);

        $recordsQuery = Attendance::query()
            ->with([
                'student.user:id,name,avatar',
                'student.department:id,name,code',
                'student.program:id,name,code',
            ])
            ->where('attendance_session_id', $attendanceSession->id)
            ->orderBy('id');

        $records = (clone $recordsQuery)
            ->paginate(15, ['*'], 'records_page')
            ->withQueryString();
        $records->setCollection(
            $records->getCollection()->sortBy(fn (Attendance $record) => Str::lower($record->student?->user?->name ?? ''))->values()
        );

        $summaryRow = Attendance::query()
            ->where('attendance_session_id', $attendanceSession->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late")
            ->selectRaw("SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused")
            ->first();

        $summary = [
            'total' => (int) ($summaryRow->total ?? 0),
            'present' => (int) ($summaryRow->present ?? 0),
            'absent' => (int) ($summaryRow->absent ?? 0),
            'late' => (int) ($summaryRow->late ?? 0),
            'excused' => (int) ($summaryRow->excused ?? 0),
        ];
        $summary['completion'] = $summary['total'] > 0 ? round(($summary['present'] / $summary['total']) * 100, 1) : 0;

        $historySessions = AttendanceSession::query()
            ->with([
                'teacher.user:id,name,avatar',
                'subject:id,name,code,type,semester,program_id,credit_hours',
                'program.department:id,name,code',
            ])
            ->withCount([
                'records',
                'records as present_records_count' => fn ($q) => $q->where('status', 'present'),
                'records as absent_records_count' => fn ($q) => $q->where('status', 'absent'),
                'records as late_records_count' => fn ($q) => $q->where('status', 'late'),
                'records as excused_records_count' => fn ($q) => $q->where('status', 'excused'),
            ])
            ->where('academic_session_id', $attendanceSession->academic_session_id)
            ->where('subject_id', $attendanceSession->subject_id)
            ->where('program_id', $attendanceSession->program_id)
            ->where('semester', $attendanceSession->semester)
            ->when($attendanceSession->section, fn ($query) => $query->where('section', $attendanceSession->section))
            ->where('id', '!=', $attendanceSession->id)
            ->orderByDesc('date')
            ->limit(8)
            ->get();

        $trendSessions = $historySessions->prepend($attendanceSession)
            ->sortBy('date')
            ->values();

        $distribution = [
            'labels' => ['Present', 'Absent', 'Late', 'Excused'],
            'values' => [
                $summary['present'],
                $summary['absent'],
                $summary['late'],
                $summary['excused'],
            ],
            'colors' => ['#10b981', '#ef4444', '#f59e0b', '#6366f1'],
        ];

        $trend = [
            'labels' => $trendSessions->map(fn (AttendanceSession $session) => bsDate($session->date, 'd F') ?: $session->date?->format('d M'))->all(),
            'values' => $trendSessions->map(function (AttendanceSession $session) {
                $total = $session->records_count ?? $session->records->count();
                $present = $session->present_records_count ?? $session->records->where('status', 'present')->count();
                return $total > 0 ? round(($present / $total) * 100, 1) : 0;
            })->all(),
        ];

        $notes = Attendance::query()
            ->where('attendance_session_id', $attendanceSession->id)
            ->whereNotNull('remarks')
            ->where('remarks', '!=', '')
            ->select('remarks')
            ->distinct()
            ->limit(4)
            ->pluck('remarks')
            ->values();

        return view('admin.attendance.session', [
            'attendanceSession' => $attendanceSession,
            'records' => $records,
            'summary' => $summary,
            'historySessions' => $historySessions,
            'distribution' => $distribution,
            'trend' => $trend,
            'notes' => $notes,
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $session = $request->filled('session_id')
            ? AcademicSession::query()->find($request->integer('session_id'))
            : (AcademicSession::current() ?: AcademicSession::query()->orderByDesc('start_date')->first());

        $range = $request->string('date_range')->toString();
        $range = in_array($range, ['today', 'week', 'month', 'custom'], true) ? $range : 'month';
        $window = $this->resolveWindow(
            $range,
            $request->string('date_from_bs')->toString(),
            $request->string('date_to_bs')->toString()
        );

        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['date', 'teacher', 'semester'], true) ? $sort : 'date';

        $direction = strtolower($request->string('direction')->toString()) === 'asc' ? 'asc' : 'desc';

        return [
            'selectedSession' => $session,
            'departmentId' => $request->integer('department_id') ?: null,
            'programId' => $request->integer('program_id') ?: null,
            'semester' => $request->integer('semester') ?: null,
            'subjectId' => $request->integer('subject_id') ?: null,
            'teacherId' => $request->integer('teacher_id') ?: null,
            'search' => trim($request->string('search')->toString()) ?: null,
            'sort' => $sort,
            'direction' => $direction,
            'dateRange' => $range,
            'dateFromBs' => $request->string('date_from_bs')->toString() ?: null,
            'dateToBs' => $request->string('date_to_bs')->toString() ?: null,
            'window' => $window,
        ];
    }

    private function resolveWindow(string $range, ?string $fromBs, ?string $toBs): array
    {
        $today = now();

        return match ($range) {
            'today' => [
                'start' => $today->copy()->startOfDay(),
                'end' => $today->copy()->endOfDay(),
                'label' => 'Today',
            ],
            'week' => [
                'start' => $today->copy()->subDays(6)->startOfDay(),
                'end' => $today->copy()->endOfDay(),
                'label' => 'This Week',
            ],
            'custom' => $this->resolveCustomWindow($fromBs, $toBs),
            default => [
                'start' => $today->copy()->subDays(29)->startOfDay(),
                'end' => $today->copy()->endOfDay(),
                'label' => 'Last 30 Days',
            ],
        };
    }

    private function resolveCustomWindow(?string $fromBs, ?string $toBs): array
    {
        $from = $fromBs ? Carbon::parse(adDate($fromBs))->startOfDay() : null;
        $to = $toBs ? Carbon::parse(adDate($toBs))->endOfDay() : null;

        if (! $from && ! $to) {
            return [
                'start' => now()->copy()->subDays(29)->startOfDay(),
                'end' => now()->copy()->endOfDay(),
                'label' => 'Last 30 Days',
            ];
        }

        $from ??= $to ? $to->copy()->startOfDay() : now()->copy()->startOfDay();
        $to ??= $from->copy()->endOfDay();

        return [
            'start' => $from,
            'end' => $to,
            'label' => 'Custom Range',
        ];
    }

    private function baseSessionQuery(array $filters, Carbon $start, Carbon $end): Builder
    {
        $query = AttendanceSession::query()
            ->with([
                'teacher.user:id,name,avatar',
                'teacher.department:id,name,code',
                'subject:id,name,code,type,semester,program_id,credit_hours',
                'program:id,department_id,name,code',
                'program.department:id,name,code',
            ])
            ->withCount([
                'records',
                'records as present_records_count' => fn ($q) => $q->where('status', 'present'),
                'records as absent_records_count' => fn ($q) => $q->where('status', 'absent'),
                'records as late_records_count' => fn ($q) => $q->where('status', 'late'),
                'records as excused_records_count' => fn ($q) => $q->where('status', 'excused'),
            ])
            ->when($filters['selectedSession'], fn ($q) => $q->where('academic_session_id', $filters['selectedSession']->id))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->when($filters['departmentId'], fn ($q) => $q->whereHas('program', fn ($programQuery) => $programQuery->where('department_id', $filters['departmentId'])))
            ->when($filters['programId'], fn ($q) => $q->where('program_id', $filters['programId']))
            ->when($filters['semester'], fn ($q) => $q->where('semester', $filters['semester']))
            ->when($filters['subjectId'], fn ($q) => $q->where('subject_id', $filters['subjectId']))
            ->when($filters['teacherId'], fn ($q) => $q->where('teacher_id', $filters['teacherId']))
            ->when($filters['search'], function ($q) use ($filters) {
                $search = $filters['search'];

                $q->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('teacher.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('records.student.user', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('records.student', fn ($studentQuery) => $studentQuery->where('student_no', 'like', "%{$search}%")->orWhere('roll_number', 'like', "%{$search}%"));
                });
            });

        return $query;
    }

    private function applySessionSorting(Builder $query, string $sort, string $direction): Builder
    {
        return match ($sort) {
            'teacher' => $query
                ->leftJoin('teachers as sort_teachers', 'sort_teachers.id', '=', 'attendance_sessions.teacher_id')
                ->leftJoin('users as sort_teacher_users', 'sort_teacher_users.id', '=', 'sort_teachers.user_id')
                ->select('attendance_sessions.*')
                ->orderBy('sort_teacher_users.name', $direction)
                ->orderByDesc('date')
                ->orderByDesc('attendance_sessions.id'),
            'semester' => $query
                ->orderBy('semester', $direction)
                ->orderByDesc('date')
                ->orderByDesc('attendance_sessions.id'),
            default => $query
                ->orderBy('date', $direction)
                ->orderByDesc('attendance_sessions.id'),
        };
    }

    private function baseRecordQuery(array $filters, Carbon $start, Carbon $end): Builder
    {
        return Attendance::query()
            ->with([
                'student.user:id,name,avatar',
                'student.department:id,name,code',
                'student.program:id,name,code',
                'attendanceSession.teacher.user:id,name,avatar',
                'attendanceSession.teacher.department:id,name,code',
                'attendanceSession.subject:id,name,code,type,semester,program_id,credit_hours',
                'attendanceSession.program.department:id,name,code',
            ])
            ->whereHas('attendanceSession', function ($q) use ($filters, $start, $end) {
                $q->when($filters['selectedSession'], fn ($sessionQuery) => $sessionQuery->where('academic_session_id', $filters['selectedSession']->id))
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->when($filters['departmentId'], fn ($sessionQuery) => $sessionQuery->whereHas('program', fn ($programQuery) => $programQuery->where('department_id', $filters['departmentId'])))
                    ->when($filters['programId'], fn ($sessionQuery) => $sessionQuery->where('program_id', $filters['programId']))
                    ->when($filters['semester'], fn ($sessionQuery) => $sessionQuery->where('semester', $filters['semester']))
                    ->when($filters['subjectId'], fn ($sessionQuery) => $sessionQuery->where('subject_id', $filters['subjectId']))
                    ->when($filters['teacherId'], fn ($sessionQuery) => $sessionQuery->where('teacher_id', $filters['teacherId']));
            })
            ->when($filters['search'], function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('student.user', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('attendanceSession.teacher.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('attendanceSession.subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            });
    }

    private function baseStudentQuery(array $filters): Builder
    {
        return Student::query()
            ->with([
                'user:id,name,avatar,email',
                'department:id,name,code',
                'program:id,name,code,total_semesters',
                'academicSession:id,name,name_bs',
            ])
            ->when($filters['selectedSession'], fn ($q) => $q->where('academic_session_id', $filters['selectedSession']->id))
            ->when($filters['departmentId'], fn ($q) => $q->where('department_id', $filters['departmentId']))
            ->when($filters['programId'], fn ($q) => $q->where('program_id', $filters['programId']))
            ->when($filters['semester'], fn ($q) => $q->where('current_semester', $filters['semester']))
            ->when($filters['search'], function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                        ->orWhere('student_no', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('roll_number', 'like', "%{$search}%");
                });
            });
    }

    private function buildCharts(Collection $sessionRows, Collection $recordsWindow, Collection $recordsTrend, Carbon $start, Carbon $end): array
    {
        return [
            'attendanceTrend' => $this->buildTrendChart($recordsTrend, now()->copy()->subDays(29)->startOfDay(), now()->copy()->endOfDay()),
            'departmentComparison' => $this->buildDepartmentComparison($recordsWindow),
            'semesterDistribution' => $this->buildSemesterDistribution($recordsWindow),
            'runningSemesters' => $sessionRows->pluck('semester')->filter()->unique()->sort()->values()->all(),
            'dateRangeLabel' => $this->windowLabel($start, $end),
        ];
    }

    private function buildKpis(Collection $sessionRows, Collection $todayRecords, Collection $yesterdayRecords, Collection $weekRecords, Collection $previousWeekRecords, Collection $weekSessionRows, Collection $previousWeekSessionRows): array
    {
        $weekTrend = $this->buildTrendChart($weekRecords, now()->copy()->subDays(6)->startOfDay(), now()->copy()->endOfDay());
        $sessionSeries = $this->buildSessionCountSeries($weekSessionRows, now()->copy()->subDays(6)->startOfDay(), now()->copy()->endOfDay());

        $attendanceToday = $this->attendanceRate($todayRecords);
        $attendanceYesterday = $this->attendanceRate($yesterdayRecords);
        $attendanceWeekly = $this->attendanceRate($weekRecords);
        $attendancePreviousWeek = $this->attendanceRate($previousWeekRecords);

        $totalScheduled = $sessionRows->count();
        $conducted = $sessionRows->where('records_count', '>', 0)->count();
        $pending = max(0, $totalScheduled - $conducted);
        $teacherReliability = $totalScheduled > 0 ? round(($conducted / $totalScheduled) * 100, 1) : 0.0;

        $previousScheduled = $previousWeekSessionRows->count();
        $previousConducted = $previousWeekSessionRows->where('records_count', '>', 0)->count();
        $previousPending = max(0, $previousScheduled - $previousConducted);
        $previousTeacherReliability = $previousScheduled > 0 ? round(($previousConducted / max($previousScheduled, 1)) * 100, 1) : 0.0;

        return [
            [
                'label' => 'Overall Attendance Rate',
                'value' => number_format($attendanceToday, 1) . '%',
                'trend' => $this->trendLabel($attendanceToday, $attendanceYesterday),
                'direction' => $this->trendDirection($attendanceToday, $attendanceYesterday),
                'sparkline' => $this->sparklinePath($weekTrend['values']),
                'tone' => 'rose',
                'note' => 'Today',
            ],
            [
                'label' => 'Weekly Average Attendance',
                'value' => number_format($attendanceWeekly, 1) . '%',
                'trend' => $this->trendLabel($attendanceWeekly, $attendancePreviousWeek),
                'direction' => $this->trendDirection($attendanceWeekly, $attendancePreviousWeek),
                'sparkline' => $this->sparklinePath($weekTrend['values']),
                'tone' => 'blue',
                'note' => 'Last 7 days',
            ],
            [
                'label' => 'Total Classes Scheduled',
                'value' => number_format($totalScheduled),
                'trend' => $this->trendLabel($totalScheduled, $previousScheduled),
                'direction' => $this->trendDirection($totalScheduled, $previousScheduled),
                'sparkline' => $this->sparklinePath($sessionSeries['values']),
                'tone' => 'emerald',
                'note' => 'Selected window',
            ],
            [
                'label' => 'Classes Conducted',
                'value' => number_format($conducted),
                'trend' => $this->trendLabel($conducted, $previousConducted),
                'direction' => $this->trendDirection($conducted, $previousConducted),
                'sparkline' => $this->sparklinePath($sessionSeries['conductedSeries']),
                'tone' => 'violet',
                'note' => 'Marked by teachers',
            ],
            [
                'label' => 'Pending Attendance',
                'value' => number_format($pending),
                'trend' => $this->trendLabel($pending, $previousPending, false),
                'direction' => $this->trendDirection($pending, $previousPending, false),
                'sparkline' => $this->sparklinePath($sessionSeries['pendingSeries']),
                'tone' => 'amber',
                'note' => 'Missing marks',
            ],
            [
                'label' => 'Teacher Reliability Score',
                'value' => number_format($teacherReliability, 1) . '%',
                'trend' => $this->trendLabel($teacherReliability, $previousTeacherReliability),
                'direction' => $this->trendDirection($teacherReliability, $previousTeacherReliability),
                'sparkline' => $this->sparklinePath($sessionSeries['completedSeries']),
                'tone' => 'slate',
                'note' => 'Completion rate',
            ],
        ];
    }

    private function buildTeacherRows(Collection $sessions): Collection
    {
        return $sessions
            ->groupBy('teacher_id')
            ->map(function (Collection $teacherSessions) {
                $teacher = $teacherSessions->first()?->teacher;
                $total = $teacherSessions->count();
                $completed = $teacherSessions->where('records_count', '>', 0)->count();
                $pending = max(0, $total - $completed);
                $reliability = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

                return [
                    'id' => $teacher?->id,
                    'teacher' => $teacher,
                    'name' => $teacher?->user?->name ?? 'Teacher',
                    'avatar' => $teacher?->user?->avatar,
                    'department' => $teacher?->department?->name,
                    'designation' => $teacher?->designation,
                    'total_sessions' => $total,
                    'completed_sessions' => $completed,
                    'pending_sessions' => $pending,
                    'reliability' => $reliability,
                    'last_session' => bsDate($teacherSessions->sortByDesc('date')->first()?->date, 'd F Y') ?: '—',
                    'status' => $reliability >= 90 ? 'Excellent' : ($reliability >= 75 ? 'Stable' : 'Needs attention'),
                ];
            })
            ->sortBy('reliability')
            ->values();
    }

    private function decorateStudentRows(Collection $students, array $filters, Carbon $start, Carbon $end): Collection
    {
        if ($students->isEmpty()) {
            return collect();
        }

        $studentIds = $students->pluck('id')->all();
        $records = $this->baseRecordQuery($filters, $start, $end)
            ->whereIn('student_id', $studentIds)
            ->get();

        $recordsByStudent = $records->groupBy('student_id');

        return $students->map(function (Student $student) use ($recordsByStudent, $start, $end) {
            $studentRecords = $recordsByStudent->get($student->id, collect());
            $total = $studentRecords->count();
            $present = $studentRecords->where('status', 'present')->count();
            $absent = $studentRecords->where('status', 'absent')->count();
            $late = $studentRecords->where('status', 'late')->count();
            $excused = $studentRecords->where('status', 'excused')->count();
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;
            $streak = $this->absentStreak($studentRecords, $start, $end);
            $risk = $rate < 60 || $streak >= 3 ? 'High' : ($rate < 75 ? 'Medium' : 'Low');
            $sparkline = $this->buildStudentDailySeries($studentRecords, $start, $end);

            return [
                'id' => $student->id,
                'student' => $student,
                'name' => $student->user?->name ?? 'Student',
                'avatar' => $student->user?->avatar,
                'program' => $student->program?->name,
                'semester' => $student->current_semester,
                'attendance_rate' => $rate,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'risk' => $risk,
                'absent_streak' => $streak,
                'sparkline' => $this->sparklinePath($sparkline['values'] ?? []),
                'last_record' => bsDate($studentRecords->sortByDesc(fn (Attendance $record) => $record->attendanceSession?->date)->first()?->attendanceSession?->date, 'd F Y') ?: '—',
            ];
        })->sortBy('attendance_rate')->values();
    }

    private function buildRules(Collection $sessions, Collection $records, Collection $students, Collection $teacherRows): array
    {
        $runningSemesters = $sessions->pluck('semester')->filter()->unique()->sort()->values();
        $lateWarnings = $sessions->filter(fn ($session) => $session->records_count === 0 && optional($session->date)->isPast())->count();
        $highRiskStudents = $students->filter(fn (array $row) => $row['risk'] === 'High')->count();
        $lowAttendanceSubjects = $records
            ->groupBy('attendance_session.subject_id')
            ->map(function (Collection $subjectRecords) {
                $total = $subjectRecords->count();
                $rate = $total > 0 ? round(($subjectRecords->where('status', 'present')->count() / $total) * 100, 1) : 0;
                return [
                    'subject' => $subjectRecords->first()?->attendanceSession?->subject?->name,
                    'rate' => $rate,
                    'count' => $total,
                ];
            })
            ->filter(fn (array $row) => $row['subject'] && $row['count'] >= 4 && $row['rate'] < 70)
            ->sortBy('rate')
            ->values();
        $slowTeachers = $teacherRows->filter(fn (array $row) => $row['pending_sessions'] > 0)->count();

        return [
            'cards' => [
                [
                    'title' => 'Running Semesters',
                    'value' => $runningSemesters->isNotEmpty() ? $runningSemesters->map(fn ($semester) => (string) $semester)->implode(' · ') : 'None',
                    'note' => 'Multi-semester CTEVT flow',
                    'tone' => 'emerald',
                ],
                [
                    'title' => 'Late Attendance Warnings',
                    'value' => number_format($lateWarnings),
                    'note' => 'Sessions not marked on time',
                    'tone' => 'amber',
                ],
                [
                    'title' => 'High-Risk Students',
                    'value' => number_format($highRiskStudents),
                    'note' => '3+ absent streak or low rate',
                    'tone' => 'rose',
                ],
                [
                    'title' => 'Teachers Needing Follow-up',
                    'value' => number_format($slowTeachers),
                    'note' => 'Sessions with pending marks',
                    'tone' => 'blue',
                ],
            ],
            'alerts' => [
                [
                    'title' => 'Low attendance subjects',
                    'message' => $lowAttendanceSubjects->isNotEmpty()
                        ? $lowAttendanceSubjects->take(3)->map(fn (array $row) => $row['subject'] . ' · ' . number_format($row['rate'], 1) . '%')->implode(', ')
                        : 'No subject has crossed the low-attendance threshold yet.',
                ],
                [
                    'title' => 'Teachers to review',
                    'message' => $teacherRows->sortBy('reliability')->take(3)->map(fn (array $row) => $row['name'] . ' · ' . number_format($row['reliability'], 1) . '%')->implode(', '),
                ],
                [
                    'title' => 'Session coverage',
                    'message' => $sessions->where('records_count', '>', 0)->count() . ' of ' . $sessions->count() . ' classes have been marked.',
                ],
            ],
        ];
    }

    private function buildTrendChart(Collection $records, Carbon $start, Carbon $end): array
    {
        return $this->buildDateSeries($records, $start, $end, function (Collection $dayRecords) {
            $total = $dayRecords->count();
            if ($total === 0) {
                return 0;
            }

            return round(($dayRecords->where('status', 'present')->count() / $total) * 100, 1);
        });
    }

    private function buildDepartmentComparison(Collection $records): array
    {
        $departments = Department::active()->orderBy('name')->get(['id', 'name', 'code']);

        $rows = $departments->map(function (Department $department) use ($records) {
            $departmentRecords = $records->filter(fn (Attendance $record) => (int) data_get($record, 'student.department_id') === (int) $department->id);
            $total = $departmentRecords->count();
            $rate = $total > 0 ? round(($departmentRecords->where('status', 'present')->count() / $total) * 100, 1) : 0;

            return [
                'id' => $department->id,
                'label' => $department->code ?: Str::limit($department->name, 18),
                'name' => $department->name,
                'rate' => $rate,
                'count' => $total,
            ];
        })->sortByDesc('rate')->values();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('rate')->all(),
            'rows' => $rows,
        ];
    }

    private function buildSemesterDistribution(Collection $records): array
    {
        $rows = $records
            ->groupBy(fn (Attendance $record) => $record->attendanceSession?->semester)
            ->map(function (Collection $semesterRecords, $semester) {
                $total = $semesterRecords->count();
                $rate = $total > 0 ? round(($semesterRecords->where('status', 'present')->count() / $total) * 100, 1) : 0;

                return [
                    'semester' => (int) $semester,
                    'label' => 'Semester ' . $semester,
                    'rate' => $rate,
                    'count' => $total,
                ];
            })
            ->filter(fn (array $row) => $row['semester'] > 0)
            ->sortBy('semester')
            ->values();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('rate')->all(),
            'rows' => $rows,
        ];
    }

    private function buildSessionCountSeries(Collection $sessions, Carbon $start, Carbon $end): array
    {
        $series = $this->buildDateSeries($sessions, $start, $end, function (Collection $daySessions) {
            $total = $daySessions->count();
            $conducted = $daySessions->where('records_count', '>', 0)->count();
            $pending = max(0, $total - $conducted);

            return [
                'value' => $total,
                'total' => $total,
                'conducted' => $conducted,
                'pending' => $pending,
            ];
        }, true);

        $series['completedSeries'] = $series['conductedSeries'];

        return $series;
    }

    private function buildStudentDailySeries(Collection $records, Carbon $start, Carbon $end): array
    {
        return $this->buildDateSeries($records, $start, $end, function (Collection $dayRecords) {
            $total = $dayRecords->count();

            return $total > 0 ? round(($dayRecords->where('status', 'present')->count() / $total) * 100, 1) : 0;
        }, true);
    }

    private function buildDateSeries(Collection $items, Carbon $start, Carbon $end, callable $resolver, bool $allowNested = false): array
    {
        $grouped = $items->groupBy(function ($item) {
            $date = data_get($item, 'attendanceSession.date') ?? data_get($item, 'date');

            return $date ? Carbon::parse($date)->toDateString() : null;
        });

        $labels = [];
        $values = [];
        $conductedSeries = [];
        $pendingSeries = [];
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $bucket = $grouped->get($key, collect());
            $result = $resolver($bucket, $key);
            $labels[] = bsDate($cursor, 'd F') ?: $cursor->format('d M');

            if (is_array($result)) {
                $values[] = (float) ($result['value'] ?? 0);
                $conductedSeries[] = (float) ($result['conducted'] ?? 0);
                $pendingSeries[] = (float) ($result['pending'] ?? 0);
            } else {
                $values[] = (float) $result;
            }

            $cursor->addDay();
        }

        $payload = [
            'labels' => $labels,
            'values' => $values,
        ];

        if ($allowNested) {
            $payload['conductedSeries'] = $conductedSeries;
            $payload['pendingSeries'] = $pendingSeries;
        }

        return $payload;
    }

    private function attendanceRate(Collection $records): float
    {
        $total = $records->count();

        return $total > 0 ? round(($records->where('status', 'present')->count() / $total) * 100, 1) : 0.0;
    }

    private function windowLabel(Carbon $start, Carbon $end): string
    {
        return $start->isSameDay($end)
            ? (bsDate($start, 'Y, F d') ?: $start->format('Y, M d'))
            : (bsDate($start, 'Y, F d') ?: $start->format('Y, M d')) . ' - ' . (bsDate($end, 'Y, F d') ?: $end->format('Y, M d'));
    }

    private function sparklinePath(array $values, int $width = 96, int $height = 28): string
    {
        if ($values === []) {
            $values = [0, 0, 0, 0, 0];
        }

        $max = max(100, max($values));
        $min = 0;
        $count = count($values);
        $stepX = $count > 1 ? $width / ($count - 1) : $width;
        $points = [];

        foreach ($values as $index => $value) {
            $x = round($stepX * $index, 2);
            $normalized = $max > $min ? (($value - $min) / ($max - $min)) : 0;
            $y = round($height - ($normalized * $height), 2);
            $points[] = [$x, $y];
        }

        $path = 'M ' . $points[0][0] . ' ' . $points[0][1];
        foreach (array_slice($points, 1) as [$x, $y]) {
            $path .= ' L ' . $x . ' ' . $y;
        }

        return $path;
    }

    private function trendLabel(float|int $current, float|int $previous, bool $asPercentage = true): string
    {
        $delta = $current - $previous;
        $sign = $delta >= 0 ? '+' : '';

        return $sign . number_format($delta, 1) . ($asPercentage ? '%' : '');
    }

    private function trendDirection(float|int $current, float|int $previous, bool $higherIsBetter = true): string
    {
        if ($current === $previous) {
            return 'flat';
        }

        $improved = $current > $previous;
        if (! $higherIsBetter) {
            $improved = ! $improved;
        }

        return $improved ? 'up' : 'down';
    }

    private function absentStreak(Collection $records, Carbon $start, Carbon $end): int
    {
        $groups = $records
            ->groupBy(fn (Attendance $record) => optional($record->attendanceSession?->date)->toDateString())
            ->map(function (Collection $dayRecords) {
                if ($dayRecords->where('status', 'present')->isNotEmpty()) {
                    return 'present';
                }

                if ($dayRecords->where('status', 'absent')->isNotEmpty()) {
                    return 'absent';
                }

                if ($dayRecords->where('status', 'late')->isNotEmpty()) {
                    return 'late';
                }

                return $dayRecords->isNotEmpty() ? 'excused' : 'none';
            })
            ->all();

        $streak = 0;
        $cursor = $end->copy()->startOfDay();
        while ($cursor->gte($start)) {
            $key = $cursor->toDateString();
            $status = $groups[$key] ?? 'none';
            if ($status === 'present' || $status === 'none') {
                if ($status === 'present') {
                    break;
                }

                $cursor->subDay();
                continue;
            }

            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }

    private function rangeOptions(): array
    {
        return [
            ['value' => 'today', 'label' => 'Today'],
            ['value' => 'week', 'label' => 'This Week'],
            ['value' => 'month', 'label' => 'This Month'],
            ['value' => 'custom', 'label' => 'Custom Range'],
        ];
    }

    /**
     * List all attendance sessions with full filter and pagination.
     */
    public function sessions(Request $request)
    {
        $query = AttendanceSession::with([
            'academicSession',
            'program.department',
            'subject',
            'teacher.user',
        ])->withCount('attendances');

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('program', fn ($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('type')) {
            $t = strtolower($request->type);
            if (in_array($t, ['lab', 'practical'])) {
                $query->where(function ($q) {
                    $q->where('period', 'LIKE', '%(Lab)%')
                      ->orWhere('period', 'LIKE', '%(Practical)%')
                      ->orWhereHas('subject', fn ($sq) => $sq->where('type', 'practical'));
                });
            } elseif (in_array($t, ['theory', 'class'])) {
                $query->where(function ($q) {
                    $q->where('period', 'LIKE', '%(Theory)%')
                      ->orWhere('period', 'LIKE', '%(Class)%')
                      ->orWhere(function ($subQ) {
                          $subQ->where('period', 'NOT LIKE', '%(Lab)%')
                               ->where('period', 'NOT LIKE', '%(Practical)%');
                      });
                });
            }
        }

        $sessions = $query->latest('date')->paginate(20)->withQueryString();

        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->where('is_active', true)->get();
        $academicSessions = AcademicSession::orderByDesc('is_active')->orderBy('name')->get();

        return view('admin.attendance.sessions', compact(
            'sessions', 'departments', 'programs', 'subjects', 'teachers', 'academicSessions'
        ));
    }

    /**
     * Show form to mark attendance.
     */
    public function mark(Request $request)
    {
        $departments = Department::with('programs')->orderBy('name')->get();
        $programs = Program::with('department')->orderBy('name')->get();

        $subjectsQuery = Subject::with('program:id,name,department_id')->where('is_active', true);
        if ($request->filled('type')) {
            $t = strtolower($request->type);
            if ($t === 'theory') {
                $subjectsQuery->whereIn('type', ['theory', 'both']);
            } elseif (in_array($t, ['lab', 'practical'])) {
                $subjectsQuery->whereIn('type', ['practical', 'both']);
            }
        }
        $subjects = $subjectsQuery->orderBy('name')->get();

        // Map teachers assigned to subjects via timetable slots or subject_teacher
        $timetableTeacherMap = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('timetable_slots')) {
            $timetableTeacherMap = \DB::table('timetable_slots')
                ->whereNotNull('subject_id')
                ->whereNotNull('teacher_id')
                ->get(['subject_id', 'teacher_id'])
                ->groupBy('subject_id')
                ->map(fn($slots) => $slots->pluck('teacher_id')->unique()->values()->all());
        }

        $subjectTeacherMap = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('subject_teacher')) {
            $subjectTeacherMap = \DB::table('subject_teacher')
                ->whereNotNull('subject_id')
                ->whereNotNull('teacher_id')
                ->get(['subject_id', 'teacher_id'])
                ->groupBy('subject_id')
                ->map(fn($rows) => $rows->pluck('teacher_id')->unique()->values()->all());
        }

        $subjects->each(function ($s) use ($timetableTeacherMap, $subjectTeacherMap) {
            $tIds = array_unique(array_merge(
                $timetableTeacherMap->get($s->id, []),
                $subjectTeacherMap->get($s->id, [])
            ));
            $s->teacher_ids = array_values($tIds);
        });

        $teachers = Teacher::with('user', 'department')->where('is_active', true)->get();
        $academicSessions = AcademicSession::orderByDesc('is_active')->orderBy('name')->get();
        $academicSession = AcademicSession::current() ?? $academicSessions->first();

        $students = collect();
        if ($request->filled('program_id') && $request->filled('semester')) {
            $studentsQuery = Student::with('user')
                ->where('program_id', $request->program_id)
                ->where('current_semester', $request->semester)
                ->where(function ($q) {
                    $q->whereIn('status', ['active', 'studying'])
                      ->orWhereNull('status');
                });

            if ($request->filled('department_id')) {
                $studentsQuery->where('department_id', $request->department_id);
            }

            if ($request->filled('section')) {
                $sec = trim((string) $request->section);
                if ($sec !== '') {
                    $studentsQuery->where(function ($q) use ($sec) {
                        $q->where('section', $sec)
                          ->orWhere('section', strtoupper($sec))
                          ->orWhere('section', strtolower($sec));
                    });
                }
            }

            $students = $studentsQuery->orderBy('roll_number')->orderBy('id')->get();
        }

        return view('admin.attendance.mark', compact(
            'departments', 'programs', 'subjects', 'teachers', 'academicSession', 'academicSessions', 'students'
        ));
    }

    /**
     * AJAX endpoint to load students for attendance marking.
     */
    public function loadStudents(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'semester'   => 'required|integer',
            'section'    => 'nullable|string',
        ]);

        $query = Student::with('user')
            ->where('program_id', $request->program_id)
            ->where('current_semester', $request->semester)
            ->where(function ($q) {
                $q->whereIn('status', ['active', 'studying'])
                  ->orWhereNull('status');
            });

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('section')) {
            $sec = trim((string) $request->section);
            if ($sec !== '') {
                $query->where(function ($q) use ($sec) {
                    $q->where('section', $sec)
                      ->orWhere('section', strtoupper($sec))
                      ->orWhere('section', strtolower($sec));
                });
            }
        }

        $students = $query->orderBy('roll_number')->orderBy('id')->get();

        return response()->json($students);
    }

    /**
     * Store new attendance session and student attendance records.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'program_id'          => 'required|exists:programs,id',
            'subject_id'          => 'required|exists:subjects,id',
            'teacher_id'          => 'required|exists:teachers,id',
            'semester'            => 'required|integer|min:1|max:6',
            'section'             => 'nullable|string|max:10',
            'date'                => 'required|string',
            'period'              => 'required|string|max:50',
            'attendance_type'     => 'nullable|in:class,lab,theory,practical',
            'attendances'         => 'required|array',
            'attendances.*'       => 'required|in:present,absent,late,excused',
            'remarks'             => 'nullable|array',
            'remarks.*'           => 'nullable|string|max:255',
        ]);

        // Support both BS and AD dates
        $dateStr = $data['date'];
        if (preg_match('/^20[789]\d/', $dateStr)) {
            $ad = \App\Helpers\NepaliDateHelper::toAD($dateStr);
            if ($ad) {
                $dateStr = $ad->format('Y-m-d');
            }
        }
        $data['date'] = $dateStr;

        $periodLabel = $data['period'];
        if (!empty($data['attendance_type'])) {
            $typeLabel = in_array(strtolower($data['attendance_type']), ['lab', 'practical']) ? 'Lab' : 'Theory';
            $cleanPeriod = trim(preg_replace('/\s*\((Theory|Lab|Class|Practical)\)/i', '', $periodLabel));
            $periodLabel = $cleanPeriod . ' (' . $typeLabel . ')';
        }

        $sessionId = null;
        \DB::transaction(function () use ($data, $periodLabel, &$sessionId) {
            $attendanceSession = AttendanceSession::create([
                'academic_session_id' => $data['academic_session_id'],
                'teacher_id'          => $data['teacher_id'],
                'subject_id'          => $data['subject_id'],
                'program_id'          => $data['program_id'],
                'semester'            => $data['semester'],
                'section'             => $data['section'] ?? null,
                'date'                => $data['date'],
                'period'              => $periodLabel,
            ]);

            $sessionId = $attendanceSession->id;

            foreach ($data['attendances'] as $studentId => $status) {
                Attendance::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'student_id'            => $studentId,
                    'status'                => $status,
                    'remarks'               => $data['remarks'][$studentId] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.attendance.sessions.show', $sessionId)
            ->with('success', 'Attendance marked and saved successfully.');
    }

    /**
     * Show form to edit an existing attendance session.
     */
    public function edit(AttendanceSession $attendanceSession)
    {
        $attendanceSession->load([
            'subject',
            'program.department',
            'teacher.user',
            'attendances.student.user',
        ]);

        $teachers = Teacher::with('user')->where('is_active', true)->get();

        return view('admin.attendance.edit', compact('attendanceSession', 'teachers'));
    }

    /**
     * Update an attendance session and student attendance records.
     */
    public function update(Request $request, AttendanceSession $attendanceSession)
    {
        $data = $request->validate([
            'teacher_id'      => 'required|exists:teachers,id',
            'date'            => 'required|string',
            'period'          => 'required|string|max:50',
            'attendance_type' => 'nullable|in:class,lab,theory,practical',
            'attendances'     => 'required|array',
            'attendances.*'   => 'required|in:present,absent,late,excused',
            'remarks'         => 'nullable|array',
            'remarks.*'       => 'nullable|string|max:255',
        ]);

        $dateStr = $data['date'];
        if (preg_match('/^20[789]\d/', $dateStr)) {
            $ad = \App\Helpers\NepaliDateHelper::toAD($dateStr);
            if ($ad) {
                $dateStr = $ad->format('Y-m-d');
            }
        }
        $data['date'] = $dateStr;

        $periodLabel = $data['period'];
        if (!empty($data['attendance_type'])) {
            $typeLabel = in_array(strtolower($data['attendance_type']), ['lab', 'practical']) ? 'Lab' : 'Theory';
            $cleanPeriod = trim(preg_replace('/\s*\((Theory|Lab|Class|Practical)\)/i', '', $periodLabel));
            $periodLabel = $cleanPeriod . ' (' . $typeLabel . ')';
        }

        \DB::transaction(function () use ($data, $periodLabel, $attendanceSession) {
            $attendanceSession->update([
                'teacher_id' => $data['teacher_id'],
                'date'       => $data['date'],
                'period'     => $periodLabel,
            ]);

            Attendance::where('attendance_session_id', $attendanceSession->id)->delete();

            foreach ($data['attendances'] as $studentId => $status) {
                Attendance::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'student_id'            => $studentId,
                    'status'                => $status,
                    'remarks'               => $data['remarks'][$studentId] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.attendance.sessions.show', $attendanceSession)
            ->with('success', 'Attendance updated successfully.');
    }

    /**
     * Delete an attendance session and all associated attendance records.
     */
    public function destroy(AttendanceSession $attendanceSession)
    {
        $attendanceSession->attendances()->delete();
        $attendanceSession->delete();

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Attendance session deleted successfully.');
    }

    /**
     * Attendance reports and analytics.
     */
    public function reports(Request $request)
    {
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $academicSessions = AcademicSession::orderByDesc('is_active')->orderBy('name')->get();

        $students = Student::query()
            ->with(['user:id,name,email,avatar', 'program:id,name,code', 'department:id,name,code'])
            ->withCount([
                'attendances as total_sessions',
                'attendances as present_sessions' => fn ($q) => $q->where('status', 'present'),
                'attendances as absent_sessions' => fn ($q) => $q->where('status', 'absent'),
                'attendances as late_sessions' => fn ($q) => $q->where('status', 'late'),
            ])
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->filled('program_id'), fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->filled('semester'), fn ($q) => $q->where('current_semester', $request->semester))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($sq) use ($term) {
                    $sq->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                       ->orWhere('student_no', 'like', "%{$term}%")
                       ->orWhere('registration_number', 'like', "%{$term}%")
                       ->orWhere('roll_number', 'like', "%{$term}%");
                });
            })
            ->paginate(25)
            ->withQueryString();

        $students->getCollection()->transform(function ($student) {
            $student->attendance_rate = $student->total_sessions > 0
                ? round(($student->present_sessions / $student->total_sessions) * 100, 1)
                : 0;
            return $student;
        });

        // Summary KPI stats
        $totalSessionsCount = AttendanceSession::count();
        $totalPresentCount = Attendance::where('status', 'present')->count();
        $totalRecordsCount = Attendance::count();
        $overallRate = $totalRecordsCount > 0 ? round(($totalPresentCount / $totalRecordsCount) * 100, 1) : 0;

        return view('admin.attendance.reports', compact(
            'departments', 'programs', 'academicSessions', 'students', 'totalSessionsCount', 'totalPresentCount', 'totalRecordsCount', 'overallRate'
        ));
    }

    /**
     * Inline toggle of individual attendance record status.
     */
    public function toggleStatus(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'required|in:present,absent,late,excused',
        ]);

        $attendance->update([
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $attendance->status,
                'message' => 'Status updated successfully.',
            ]);
        }

        return back()->with('success', 'Attendance record updated.');
    }
}
