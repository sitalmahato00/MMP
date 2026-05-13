<?php

namespace App\Modules\Attendance\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\CMS\Models\Page;
use App\Modules\Department\Models\Department;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
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
}
