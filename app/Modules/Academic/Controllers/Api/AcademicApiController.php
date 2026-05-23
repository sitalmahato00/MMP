<?php

namespace App\Modules\Academic\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\AcademicSessionSemester;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\CMS\Models\Notice;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Mark;
use App\Modules\Parent\Models\ParentModel;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\Alumni\Models\Alumni;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AcademicApiController extends BaseController
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/dashboard/stats
     */
    public function dashboardStats(): JsonResponse
    {
        $stats = [
            'total_students'   => Student::count(),
            'active_students'  => Student::where('status', 'active')->count(),
            'total_teachers'   => Teacher::count(),
            'active_teachers'  => Teacher::where('status', 'active')->count(),
            'total_departments' => Department::count(),
            'current_session'  => AcademicSession::current(),
        ];

        return $this->success($stats);
    }

    private function greeting(): string
    {
        $h = Carbon::now()->hour;
        return match (true) { $h < 12 => 'Good morning', $h < 17 => 'Good afternoon', default => 'Good evening' };
    }

    /**
     * GET /api/v1/dashboard/admin — Rich admin dashboard data
     */
    public function adminDashboard(): JsonResponse
    {
        $now = Carbon::now();
        $activeSession = AcademicSession::current();
        $currentStudents = Student::active()->count();
        $totalTeachers = Teacher::active()->count();
        $totalParents = ParentModel::count();
        $totalAlumni = Alumni::count();

        // Attendance summary (last 30 days)
        $attRow = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->whereBetween('attendance_sessions.date', [$now->copy()->subDays(29)->toDateString(), $now->toDateString()])
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
            ->first();
        $attTotal = (int) ($attRow->total ?? 0);
        $attPresent = (int) ($attRow->present ?? 0);
        $attRate = $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 1) : 0;

        // Pass summary
        $publishedMarks = Mark::published();
        $passTotal = (clone $publishedMarks)->count();
        $passPassed = (clone $publishedMarks)->where('is_absent', false)->where('is_withheld', false)->count();
        $passRate = $passTotal > 0 ? round(($passPassed / $passTotal) * 100, 1) : 0;

        // Grade distribution — compute from assessment marks where available
        $grades = ['A' => 0, 'B' => 0, 'C' => 0, 'Fail' => 0];
        $gradedMarks = Mark::published()
            ->whereNotNull('assessment_obtained_marks')
            ->where('assessment_full_marks', '>', 0)
            ->get(['assessment_obtained_marks', 'assessment_full_marks', 'is_absent', 'is_withheld']);
        foreach ($gradedMarks as $m) {
            if ($m->is_absent || $m->is_withheld) continue;
            $pct = ((float) $m->assessment_obtained_marks / (float) $m->assessment_full_marks) * 100;
            if ($pct >= 80) $grades['A']++;
            elseif ($pct >= 70) $grades['B']++;
            elseif ($pct >= 60) $grades['C']++;
            else $grades['Fail']++;
        }
        $hasGradeData = array_sum($grades) > 0;

        // Recent notices
        $recentNotices = Notice::published()
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'created_at']);

        // Running semesters
        $semesters = [];
        if ($activeSession) {
            $semesters = AcademicSessionSemester::where('academic_session_id', $activeSession->id)
                ->orderBy('semester_number')
                ->get()
                ->map(fn ($s) => ['number' => $s->semester_number, 'status' => $s->status])
                ->toArray();
        }

        // Attendance chart data (7 & 30 days)
        $chartData = [];
        foreach ([7, 30] as $days) {
            $labels = [];
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $labels[] = $date->format('M d');
                $row = Attendance::query()
                    ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                    ->where('attendance_sessions.date', $date->toDateString())
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                    ->first();
                $t = (int) ($row->total ?? 0);
                $p = (int) ($row->present ?? 0);
                $data[] = $t > 0 ? round(($p / $t) * 100, 1) : 0;
            }
            $chartData[(string) $days] = ['labels' => $labels, 'data' => $data];
        }

        // Alerts
        $alerts = [];
        if ($attRate > 0 && $attRate < 75) {
            $alerts[] = ['title' => 'Low Attendance Rate', 'message' => "Attendance is at {$attRate}% — below the 75% threshold. Consider sending reminders.", 'tone' => 'danger', 'actionLabel' => 'View Attendance', 'actionHref' => '/admin/attendance'];
        }
        if ($passRate > 0 && $passRate < 60) {
            $alerts[] = ['title' => 'Low Pass Rate', 'message' => "Pass rate is at {$passRate}%. Review exam difficulty and student support.", 'tone' => 'warning', 'actionLabel' => 'View Exams', 'actionHref' => '/admin/exams'];
        }
        $totalDepts = Department::active()->count();
        if ($totalDepts > 0 && $totalTeachers < $totalDepts) {
            $alerts[] = ['title' => 'Understaffed Departments', 'message' => "Some departments have no assigned teachers ({$totalTeachers} teachers for {$totalDepts} departments).", 'tone' => 'warning', 'actionLabel' => 'View Teachers', 'actionHref' => '/admin/teachers'];
        }
        $totalStudents = Student::active()->count();
        if ($totalStudents > 0 && $totalParents < $totalStudents) {
            $alerts[] = ['title' => 'Parent Accounts Missing', 'message' => "Only {$totalParents} parent accounts for {$totalStudents} students. Some parents may not have portal access.", 'tone' => 'info', 'actionLabel' => 'View Parents', 'actionHref' => '/admin/parents'];
        }

        $dashboard = [
            'greeting' => $this->greeting(),
            'sessionLabel' => $activeSession?->name ?? 'Current session',
            'rangeLabel' => $now->copy()->subDays(29)->format('M d, Y') . ' – ' . $now->format('M d, Y'),
            'updatedAt' => $now->format('M d, Y, h:i A'),
            'kpis' => [
                ['key' => 'students', 'title' => 'Total Active Users', 'value' => (string) ($currentStudents + $totalTeachers + $totalParents), 'suffix' => null, 'note' => 'Students + Teachers + Parents', 'tone' => 'blue'],
                ['key' => 'attendance', 'title' => 'Attendance Rate', 'value' => (string) $attRate, 'suffix' => '%', 'note' => number_format($attPresent) . ' / ' . number_format($attTotal) . ' present', 'tone' => 'emerald'],
                ['key' => 'pass', 'title' => 'Pass Rate', 'value' => (string) $passRate, 'suffix' => '%', 'note' => number_format($passPassed) . ' / ' . number_format($passTotal) . ' marks', 'tone' => 'violet'],
                ['key' => 'departments', 'title' => 'Total Departments', 'value' => (string) $totalDepts, 'suffix' => null, 'note' => 'Active departments', 'tone' => 'amber'],
            ],
            'runningSemesters' => $semesters,
            'recentNotices' => $recentNotices->map(fn ($n) => [
                'title' => $n->title,
                'date' => $n->created_at->format('M d, Y'),
                'href' => null,
            ]),
            'alerts' => $alerts,
            'highlight' => [
                'quickStats' => ['teachers' => $totalTeachers, 'parents' => $totalParents, 'alumni' => $totalAlumni],
            ],
            'attendanceChartData' => $chartData,
            'gradeDistribution' => $hasGradeData
                ? ['hasData' => true, 'labels' => ['A (80%+)', 'B (70-79%)', 'C (60-69%)', 'Fail (<60%)'], 'data' => [$grades['A'], $grades['B'], $grades['C'], $grades['Fail']]]
                : ['hasData' => false],
            'totalTeachers' => $totalTeachers,
            'totalParents' => $totalParents,
            'totalAlumni' => $totalAlumni,
        ];

        return response()->json($dashboard);
    }

    // ─── Sessions ─────────────────────────────────────────────────────────────

    public function sessions(): JsonResponse
    {
        return $this->success(AcademicSession::orderByDesc('id')->limit(20)->get());
    }

    public function currentSession(): JsonResponse
    {
        $session = AcademicSession::current();
        if (!$session) return $this->notFound('No active session found.');
        return $this->success($session);
    }

    public function storeSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100', 'unique:academic_sessions,name'],
            'name_bs'    => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
        ]);

        if (!empty($data['is_current'])) {
            AcademicSession::query()->update(['is_current' => false]);
        }

        $session = AcademicSession::create($data);
        return $this->created($session);
    }

    // ─── Programs ─────────────────────────────────────────────────────────────

    public function programs(Request $request): JsonResponse
    {
        $programs = Program::query()
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->orderBy('name')
            ->get();

        return $this->success($programs);
    }

    // ─── Departments ──────────────────────────────────────────────────────────

    public function departments(): JsonResponse
    {
        return $this->success(Department::orderBy('name')->get(['id', 'name', 'code']));
    }

    // ─── Subjects ─────────────────────────────────────────────────────────────

    public function subjects(Request $request): JsonResponse
    {
        $subjects = Subject::query()
            ->when($request->program_id,  fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester,    fn ($q) => $q->where('semester', $request->semester))
            ->orderBy('name')
            ->get();

        return $this->success($subjects);
    }
}
