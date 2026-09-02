<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\{Student, Teacher, Department, AcademicSession, Notice, Attendance, AttendanceSession, Mark, Program};
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    protected PublicDataService $publicDataService;

    public function __construct(PublicDataService $publicDataService)
    {
        $this->publicDataService = $publicDataService;
    }
    
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Find department where this user is HOD
        $department = Department::where('hod_id', $user->id)->first();
        
        // Get department ID (null if not assigned)
        $deptId = $department?->id;
        $session = AcademicSession::current();

        // If no department assigned, show dashboard with empty/zero data
        if (!$deptId) {
            $data = [
                'student_count' => 0,
                'teacher_count' => 0,
                'program_count' => 0,
                'attendance_rate' => 0,
                'total_marks' => 0,
            ];
            
            $recentNotices = Notice::published()
                ->whereNull('department_id')
                ->forNoticeBoard()
                ->with(['author'])
                ->latest()
                ->take(5)
                ->get();
            
            $greeting = $this->greeting();
            $lastUpdated = now();
            
            return view('hod.dashboard', compact(
                'data',
                'department',
                'session',
                'recentNotices',
                'greeting',
                'lastUpdated'
            ));
        }

        $cacheKey = "hod_dashboard_{$deptId}_v3";
        $dashboardData = Cache::remember($cacheKey, 120, function () use ($deptId, $session) {
            $studentCount = Student::active()->where('department_id', $deptId)->count();
            $teacherCount = Teacher::active()->where('department_id', $deptId)->count();
            $programCount = Program::where('department_id', $deptId)->count();

            // Attendance rate for department (last 7 days)
            $sevenDaysAgo = Carbon::now()->subDays(7);
            $attendanceData = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->where('students.department_id', $deptId)
                ->where('attendance_sessions.date', '>=', $sevenDaysAgo->toDateString())
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            $attendanceRate = $attendanceData && $attendanceData->total > 0
                ? round(($attendanceData->present / $attendanceData->total) * 100, 1)
                : 0;

            // Running semesters in department
            $semesters = Student::active()
                ->where('department_id', $deptId)
                ->whereNotNull('semester')
                ->distinct()
                ->orderBy('semester')
                ->pluck('semester')
                ->map(fn ($s) => 'Sem ' . $s)
                ->values()
                ->all();

            if (empty($semesters)) {
                $semesters = ['Sem 1', 'Sem 3'];
            }

            // Attendance Chart Data (7 Days, 30 Days, Session)
            $chart7Days = Carbon::now()->subDays(6)->toDateString();
            $raw7 = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->where('students.department_id', $deptId)
                ->where('attendance_sessions.date', '>=', $chart7Days)
                ->selectRaw("attendance_sessions.date as att_date, COUNT(*) as total, SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->groupBy('attendance_sessions.date')
                ->orderBy('attendance_sessions.date')
                ->get()
                ->keyBy('att_date');

            $labels7 = [];
            $data7   = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = Carbon::now()->subDays($i);
                $row = $raw7->get($d->toDateString());
                $labels7[] = bsDate($d, 'd F');
                $data7[]   = ($row && $row->total > 0) ? round(($row->present / $row->total) * 100, 1) : 0;
            }

            // 30 Days
            $chart30Days = Carbon::now()->subDays(29)->toDateString();
            $raw30 = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->where('students.department_id', $deptId)
                ->where('attendance_sessions.date', '>=', $chart30Days)
                ->selectRaw("attendance_sessions.date as att_date, COUNT(*) as total, SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->groupBy('attendance_sessions.date')
                ->orderBy('attendance_sessions.date')
                ->get()
                ->keyBy('att_date');

            $labels30 = [];
            $data30   = [];
            for ($i = 29; $i >= 0; $i -= 3) {
                $d = Carbon::now()->subDays($i);
                $row = $raw30->get($d->toDateString());
                $labels30[] = bsDate($d, 'd F');
                $data30[]   = ($row && $row->total > 0) ? round(($row->present / $row->total) * 100, 1) : 0;
            }

            $attendanceChartData = [
                '7'       => ['labels' => $labels7, 'data' => $data7],
                '30'      => ['labels' => $labels30, 'data' => $data30],
                'session' => ['labels' => $labels7, 'data' => $data7],
            ];

            // Grade Distribution for department
            $marks = Mark::query()
                ->join('students', 'students.id', '=', 'marks.student_id')
                ->where('students.department_id', $deptId)
                ->where('students.is_active', true)
                ->where('marks.status', 'published')
                ->select([
                    'marks.internal_theory_marks',
                    'marks.external_theory_marks',
                    'marks.internal_practical_marks',
                    'marks.external_practical_marks',
                    'marks.assessment_obtained_marks',
                ])
                ->get();

            $gradeCounts = ['A+' => 0, 'A' => 0, 'B+' => 0, 'B' => 0, 'C' => 0, 'F' => 0];
            $totalMarksCount = $marks->count();
            foreach ($marks as $mark) {
                $total = (($mark->internal_theory_marks ?? 0)
                    + ($mark->external_theory_marks ?? 0)
                    + ($mark->internal_practical_marks ?? 0)
                    + ($mark->external_practical_marks ?? 0)
                    + ($mark->assessment_obtained_marks ?? 0));

                $g = match (true) {
                    $total >= 90 => 'A+',
                    $total >= 80 => 'A',
                    $total >= 70 => 'B+',
                    $total >= 60 => 'B',
                    $total >= 50 => 'C',
                    default      => 'F',
                };
                $gradeCounts[$g]++;
            }

            $defaultCounts = [1, 4, 2, 3, 0, 0];
            $counts = $totalMarksCount > 0 ? array_values($gradeCounts) : $defaultCounts;
            $sumCounts = array_sum($counts);
            $pcts = $sumCounts > 0 ? array_map(fn ($c) => round(($c / $sumCounts) * 100, 1), $counts) : [10, 40, 20, 30, 0, 0];

            $gradeDistribution = [
                'labels'  => ['A+ (90-100)', 'A (80-89)', 'B+ (70-79)', 'B (60-69)', 'C (50-59)', 'F (<50)'],
                'data'    => $pcts,
                'counts'  => $counts,
                'hasData' => $totalMarksCount > 0,
            ];

            return [
                'data' => [
                    'student_count'   => $studentCount,
                    'teacher_count'   => $teacherCount,
                    'program_count'   => $programCount,
                    'attendance_rate' => $attendanceRate,
                ],
                'attendanceChartData' => $attendanceChartData,
                'gradeDistribution'   => $gradeDistribution,
                'runningSemesters'    => $semesters,
            ];
        });

        $data = $dashboardData['data'];
        $attendanceChartData = $dashboardData['attendanceChartData'];
        $gradeDistribution   = $dashboardData['gradeDistribution'];
        $runningSemesters    = $dashboardData['runningSemesters'];

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('hod.dashboard', compact(
            'data',
            'department',
            'session',
            'attendanceChartData',
            'gradeDistribution',
            'runningSemesters',
            'greeting',
            'lastUpdated'
        ));
    }

    private function getChartData($deptId): array
    {
        return Cache::remember("hod_chart_{$deptId}_v1", 300, function () use ($deptId) {
            // ── 1. Grade distribution ─────────────────────────────────────────────
            // Select only the 6 columns needed; replicate total_marks logic inline
            // instead of eager-loading full exam objects.
            $marks = Mark::query()
                ->join('students', 'students.id', '=', 'marks.student_id')
                ->join('exams',    'exams.id',    '=', 'marks.exam_id')
                ->where('students.department_id', $deptId)
                ->where('students.is_active', true)
                ->where('marks.status', 'published')
                ->select([
                    'marks.internal_theory_marks',
                    'marks.external_theory_marks',
                    'marks.internal_practical_marks',
                    'marks.external_practical_marks',
                    'marks.assessment_obtained_marks',
                    'exams.category as exam_category',
                ])
                ->get();

            $gradeDistribution = ['A+' => 0, 'A' => 0, 'B+' => 0, 'B' => 0, 'C' => 0, 'F' => 0];
            foreach ($marks as $mark) {
                $total = ($mark->exam_category === 'monthly_assessment' && $mark->assessment_obtained_marks !== null)
                    ? (float) $mark->assessment_obtained_marks
                    : (($mark->internal_theory_marks   ?? 0)
                     + ($mark->external_theory_marks   ?? 0)
                     + ($mark->internal_practical_marks ?? 0)
                     + ($mark->external_practical_marks ?? 0));

                $grade = match (true) {
                    $total >= 90 => 'A+',
                    $total >= 80 => 'A',
                    $total >= 70 => 'B+',
                    $total >= 60 => 'B',
                    $total >= 50 => 'C',
                    default      => 'F',
                };
                $gradeDistribution[$grade]++;
            }

            // ── 2. Attendance trend – 1 grouped query instead of 7 ───────────────
            $sevenDaysAgo  = Carbon::now()->subDays(6)->toDateString();
            $rawAttendance = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students',            'students.id',            '=', 'attendances.student_id')
                ->where('students.department_id', $deptId)
                ->where('attendance_sessions.date', '>=', $sevenDaysAgo)
                ->selectRaw("attendance_sessions.date as att_date,
                             COUNT(*) as total,
                             SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->groupBy('attendance_sessions.date')
                ->orderBy('attendance_sessions.date')
                ->get()
                ->keyBy('att_date');

            $attendanceData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $row  = $rawAttendance->get($date->toDateString());
                if ($row && $row->total > 0) {
                    $attendanceData[] = [
                        'date'       => $date->toDateString(),
                        'date_bs'    => bsDate($date, 'Y F d, l'),
                        'date_short' => bsDate($date, 'F d'),
                        'rate'       => round(($row->present / $row->total) * 100, 1),
                    ];
                }
            }

            // ── 3. Today's classes – pre-load attendance in 2 queries (no N+1) ───
            $today     = strtolower(Carbon::now()->format('l'));
            $todayDate = Carbon::now()->toDateString();

            $slots = \App\Models\TimetableSlot::query()
                ->join('timetables', 'timetables.id', '=', 'timetable_slots.timetable_id')
                ->join('subjects',   'subjects.id',   '=', 'timetable_slots.subject_id')
                ->join('teachers',   'teachers.id',   '=', 'timetable_slots.teacher_id')
                ->join('users',      'users.id',      '=', 'teachers.user_id')
                ->join('programs',   'programs.id',   '=', 'timetables.program_id')
                ->where('programs.department_id',       $deptId)
                ->where('timetable_slots.day_of_week',  $today)
                ->where('timetables.is_active',         true)
                ->select([
                    'timetable_slots.start_time',
                    'timetable_slots.end_time',
                    'timetable_slots.room_number',
                    'timetable_slots.type',
                    'subjects.id   as subject_id',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code',
                    'users.name    as teacher_name',
                    'timetables.semester',
                    'timetables.section',
                    'timetables.program_id',
                    'programs.name as program_name',
                    'programs.code as program_code',
                ])
                ->orderBy('timetable_slots.start_time')
                ->get();

            // Pre-load all today's attendance sessions for relevant programs (1 query)
            $programIds = $slots->pluck('program_id')->unique()->filter()->values()->all();
            $sessions   = collect();
            $statsMap   = [];
            if (!empty($programIds)) {
                $sessions = \App\Models\AttendanceSession::where('date', $todayDate)
                    ->whereIn('program_id', $programIds)
                    ->get()
                    ->keyBy(fn($s) => $s->program_id . '|' . $s->semester . '|' . $s->subject_id . '|' . ($s->section ?? ''));

                // Pre-load attendance stats for those sessions (1 query)
                $sessionIds = $sessions->pluck('id')->filter()->all();
                if (!empty($sessionIds)) {
                    \App\Models\Attendance::whereIn('attendance_session_id', $sessionIds)
                        ->selectRaw("attendance_session_id,
                                     COUNT(*) as total,
                                     SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                                     SUM(CASE WHEN status = 'absent'  THEN 1 ELSE 0 END) as absent")
                        ->groupBy('attendance_session_id')
                        ->get()
                        ->each(fn($s) => $statsMap[$s->attendance_session_id] = $s);
                }
            }

            $todayClasses = $slots->map(function ($class) use ($sessions, $statsMap) {
                $key     = $class->program_id . '|' . $class->semester . '|' . $class->subject_id . '|' . ($class->section ?? '');
                $session = $sessions->get($key);
                $stats   = $session ? ($statsMap[$session->id] ?? null) : null;

                $attendanceMarked    = false;
                $totalStudentsMarked = 0;
                $presentCount        = 0;
                $absentCount         = 0;

                if ($stats && $stats->total > 0) {
                    $attendanceMarked    = true;
                    $totalStudentsMarked = $stats->total;
                    $presentCount        = $stats->present ?? 0;
                    $absentCount         = $stats->absent  ?? 0;
                }

                return [
                    'time'                  => Carbon::parse($class->start_time)->format('g:i A') . ' - ' . Carbon::parse($class->end_time)->format('g:i A'),
                    'subject'               => $class->subject_name,
                    'subject_code'          => $class->subject_code,
                    'teacher'               => $class->teacher_name,
                    'room'                  => $class->room_number,
                    'type'                  => ucfirst($class->type),
                    'program'               => $class->program_code . ' - Sem ' . $class->semester . ($class->section ? ' (' . $class->section . ')' : ''),
                    'program_full'          => $class->program_name . ' (Semester ' . $class->semester . ($class->section ? ', Section ' . $class->section : '') . ')',
                    'attendance_marked'     => $attendanceMarked,
                    'total_students_marked' => $totalStudentsMarked,
                    'present_count'         => $presentCount,
                    'absent_count'          => $absentCount,
                    'attendance_rate'       => $totalStudentsMarked > 0 ? round(($presentCount / $totalStudentsMarked) * 100, 1) : 0,
                ];
            });

            return [
                'grades'       => $gradeDistribution,
                'attendance'   => $attendanceData,
                'todayClasses' => $todayClasses,
            ];
        });
    }

    private function greeting(): string
    {
        $hour = Carbon::now()->hour;
        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening'
        };
    }
}
