<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Assignment, Attendance, Notice};
use App\Services\PublicDataService;
use App\Services\StudentRecordService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected PublicDataService $publicDataService;
    protected StudentRecordService $studentRecordService;

    public function __construct(PublicDataService $publicDataService, StudentRecordService $studentRecordService)
    {
        $this->publicDataService = $publicDataService;
        $this->studentRecordService = $studentRecordService;
    }

    public function index()
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $session = AcademicSession::current();
        $marksSummary = $this->getMarksSummary($student);
        $attendanceSummary = $this->getAttendanceSummary($student);

        $kpiData = $this->getKpiData($student, $marksSummary, $attendanceSummary);
        $notices = $this->getNoticesData($student);

        $upcomingAssignments = Cache::remember("student_upcoming_assignments_{$student->id}_v1", 180, function () use ($student) {
            return Assignment::where('program_id', $student->program_id)
                ->where('semester', $student->current_semester)
                ->where('due_date', '>=', now())
                ->whereDoesntHave('submissions', fn ($query) => $query->where('student_id', $student->id))
                ->with('subject')
                ->orderBy('due_date')
                ->take(5)
                ->get();
        });

        $chartData = Cache::remember("student_chart_{$student->id}_v3", 300, function () use ($student, $marksSummary) {
            return $this->getChartData($student, $marksSummary);
        });
        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('student.dashboard', compact(
            'student',
            'session',
            'notices',
            'kpiData',
            'chartData',
            'upcomingAssignments',
            'greeting',
            'lastUpdated'
        ));
    }

    private function getChartData($student, array $marksSummary): array
    {
        return [
            'attendance'     => $this->getAttendanceChartData($student),
            'grades'         => $this->getGradeDistribution($marksSummary),
            'semester_marks' => $this->getSemesterMarksChart($student),
        ];
    }

    /**
     * Per-semester bar chart: CTEVT % vs Assessment % side-by-side.
     * Always shows all 6 semesters; semesters without data show null (bar hidden).
     */
    private function getSemesterMarksChart($student): array
    {
        $marks = $this->studentRecordService->getVisiblePublishedMarks($student);

        // Pre-group by semester for fast lookup
        $bySemester = $marks->groupBy('semester');

        $labels         = [];
        $ctevtData      = [];
        $assessmentData = [];

        for ($sem = 1; $sem <= 6; $sem++) {
            $labels[] = 'Sem ' . $sem;

            $semMarks = $bySemester->get($sem, collect());

            if ($semMarks->isEmpty()) {
                $ctevtData[]      = null;
                $assessmentData[] = null;
                continue;
            }

            $ctevtObtained = 0.0;  $ctevtFull = 0.0;
            $asmtObtained  = 0.0;  $asmtFull  = 0.0;

            foreach ($semMarks as $mark) {
                $isAssessment = ($mark->exam?->category ?? '') === 'monthly_assessment';
                $obtained = ($mark->internal_theory_marks    ?? 0)
                          + ($mark->external_theory_marks    ?? 0)
                          + ($mark->internal_practical_marks ?? 0)
                          + ($mark->external_practical_marks ?? 0);
                $full = (float) (($mark->subject?->full_marks_internal_theory   ?? 0)
                      + ($mark->subject?->full_marks_external_theory             ?? 0)
                      + ($mark->subject?->full_marks_internal_practical          ?? 0)
                      + ($mark->subject?->full_marks_external_practical          ?? 0));

                if ($isAssessment) {
                    $asmtObtained += $obtained;
                    $asmtFull     += ($mark->assessment_full_marks ?? $full);
                } else {
                    $ctevtObtained += $obtained;
                    $ctevtFull     += $full;
                }
            }

            $ctevtData[]      = $ctevtFull  > 0 ? round(($ctevtObtained  / $ctevtFull)  * 100, 1) : null;
            $assessmentData[] = $asmtFull   > 0 ? round(($asmtObtained   / $asmtFull)   * 100, 1) : null;
        }

        return [
            'labels'     => $labels,
            'ctevt'      => $ctevtData,
            'assessment' => $assessmentData,
        ];
    }

    private function getAttendanceChartData($student): array
    {
        $sevenDaysAgo = Carbon::now()->subDays(6)->toDateString();

        // 1 grouped query instead of 7 separate ones
        $rows = Attendance::where('student_id', $student->id)
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->where('attendance_sessions.date', '>=', $sevenDaysAgo)
            ->selectRaw("attendance_sessions.date as att_date,
                         COUNT(*) as total,
                         SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
            ->groupBy('attendance_sessions.date')
            ->get()
            ->keyBy('att_date');

        $attendanceData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $row  = $rows->get($date->toDateString());
            $total   = (int) ($row->total   ?? 0);
            $present = (int) ($row->present ?? 0);

            $attendanceData[] = [
                'date'         => $date->toDateString(),
                'date_bs'      => bsDate($date, 'Y F d, l'),
                'date_bs_short'=> bsDate($date, 'F d'),
                'date_short'   => $date->format('M j'),
                'rate'         => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
            ];
        }

        return $attendanceData;
    }

    private function getGradeDistribution(array $marksSummary): array
    {
        $distribution = $marksSummary['grade_distribution'] ?? [];

        return [
            'labels' => [
                'Distinction (80%+)',
                'First Division (60-79%)',
                'Second Division (45-59%)',
                'Third Division (32-44%)',
                'Fail (<32%)',
            ],
            'data' => [
                (int) ($distribution['distinction'] ?? 0),
                (int) ($distribution['first_division'] ?? 0),
                (int) ($distribution['second_division'] ?? 0),
                (int) ($distribution['third_division'] ?? 0),
                (int) ($distribution['fail'] ?? 0),
            ],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#6b7280'],
        ];
    }

    private function getKpiData($student, array $marksSummary, array $attendanceSummary): array
    {
        $cacheKey = "student_dashboard_kpi_{$student->id}_v5";

        return Cache::remember($cacheKey, 300, function () use ($student, $marksSummary, $attendanceSummary) {
            $pendingAssignments = Assignment::where('program_id', $student->program_id)
                ->where('semester', $student->current_semester)
                ->where('due_date', '>=', now())
                ->whereDoesntHave('submissions', fn ($query) => $query->where('student_id', $student->id))
                ->count();

            return [
                'attendance_rate' => (float) ($attendanceSummary['rate'] ?? 0.0),
                'pending_assignments' => (int) $pendingAssignments,
                'percentage_rate' => (float) ($marksSummary['percentage_rate'] ?? 0.0),
                'published_assessments' => (int) ($marksSummary['total_assessments'] ?? 0),
                'distinction_assessments' => (int) ($marksSummary['distinction_assessments'] ?? 0),
                'total_subjects' => (int) ($marksSummary['total_subjects'] ?? 0),
            ];
        });
    }

    private function getMarksSummary($student): array
    {
        $cacheKey = "student_dashboard_marks_summary_{$student->id}_v2";

        return Cache::remember($cacheKey, 300, function () use ($student) {
            $marks = $this->studentRecordService->getVisiblePublishedMarks($student);

            return $this->studentRecordService->summarizeMarks($marks);
        });
    }

    private function getAttendanceSummary($student): array
    {
        $cacheKey = "student_dashboard_attendance_summary_{$student->id}_v1";

        return Cache::remember($cacheKey, 300, function () use ($student) {
            return $this->studentRecordService->getAttendanceSummary($student);
        });
    }

    private function getNoticesData($student)
    {
        $cacheKey = "student_dashboard_notices_{$student->department_id}_v4";

        return Cache::remember($cacheKey, 300, function () use ($student) {
            return Notice::where('is_published', true)
                ->visibleToStudent($student)
                ->forNoticeBoard()
                ->with('author')
                ->latest()
                ->take(5)
                ->get();
        });
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
}
