<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the centralized Reports Hub.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'attendance'); // attendance, students, exams, marks, subjects

        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $academicSessions = AcademicSession::orderByDesc('is_active')->get();
        $exams = Exam::orderByDesc('id')->get();

        $departmentId = $request->get('department_id');
        $programId = $request->get('program_id');
        $semester = $request->get('semester');
        $academicSessionId = $request->get('academic_session_id');
        $examId = $request->get('exam_id');
        $search = trim((string) $request->get('search'));

        $data = [];
        $kpis = [];
        $charts = [];

        switch ($type) {
            case 'students':
                $query = Student::with(['user', 'department', 'program', 'academicSession'])
                    ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('current_semester', $semester))
                    ->when($academicSessionId, fn ($q) => $q->where('academic_session_id', $academicSessionId))
                    ->when($search, function ($q) use ($search) {
                        $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                          ->orWhere('student_no', 'like', "%{$search}%")
                          ->orWhere('roll_number', 'like', "%{$search}%")
                          ->orWhere('registration_number', 'like', "%{$search}%");
                    });

                $data = $query->paginate(25)->withQueryString();

                $totalStudents = Student::count();
                $activeStudents = Student::where('status', 'active')->count();
                $kpis = [
                    ['label' => 'Total Enrolled', 'value' => number_format($totalStudents)],
                    ['label' => 'Active Students', 'value' => number_format($activeStudents)],
                    ['label' => 'Filtered Count', 'value' => number_format($data->total())],
                ];

                $progLabels = [];
                $progCounts = [];
                foreach ($programs as $pr) {
                    $c = Student::where('program_id', $pr->id)->count();
                    if ($c > 0) {
                        $progLabels[] = $pr->code ?: $pr->name;
                        $progCounts[] = $c;
                    }
                }
                $semLabels = [];
                $semCounts = [];
                for ($s = 1; $s <= 6; $s++) {
                    $semLabels[] = 'Sem ' . $s;
                    $semCounts[] = Student::where('current_semester', $s)->count();
                }
                $charts = [
                    'chart1' => [
                        'title' => 'Program-wise Enrollment Breakdown',
                        'type' => 'bar',
                        'labels' => !empty($progLabels) ? $progLabels : ['No Data'],
                        'data' => !empty($progCounts) ? $progCounts : [0],
                        'color' => '#8B0000',
                    ],
                    'chart2' => [
                        'title' => 'Semester Distribution (Sem 1–6)',
                        'type' => 'doughnut',
                        'labels' => $semLabels,
                        'data' => $semCounts,
                        'colors' => ['#8B0000', '#B91C1C', '#DC2626', '#EA580C', '#F97316', '#FBBF24'],
                    ],
                ];
                break;

            case 'exams':
                $query = Exam::with(['academicSession', 'department', 'programs'])
                    ->withCount('marks')
                    ->when($academicSessionId, fn ($q) => $q->where('academic_session_id', $academicSessionId))
                    ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                    ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"));

                $data = $query->latest('id')->paginate(20)->withQueryString();

                $totalExams = Exam::count();
                $publishedExams = Exam::where('is_published', true)->count();
                $kpis = [
                    ['label' => 'Total Exams', 'value' => number_format($totalExams)],
                    ['label' => 'Published Results', 'value' => number_format($publishedExams)],
                    ['label' => 'Filtered Exams', 'value' => number_format($data->total())],
                ];

                $typeCounts = Exam::select('type', DB::raw('count(*) as count'))->groupBy('type')->pluck('count', 'type')->all();
                $charts = [
                    'chart1' => [
                        'title' => 'Examination Categories Breakdown',
                        'type' => 'doughnut',
                        'labels' => !empty($typeCounts) ? array_map('ucfirst', array_keys($typeCounts)) : ['Terminal', 'Final', 'Assessment'],
                        'data' => !empty($typeCounts) ? array_values($typeCounts) : [0, 0, 0],
                        'colors' => ['#3B82F6', '#8B0000', '#10B981', '#F59E0B'],
                    ],
                    'chart2' => [
                        'title' => 'Exam Results Publication Status',
                        'type' => 'bar',
                        'labels' => ['Published Results', 'Drafts / Pending'],
                        'data' => [$publishedExams, max(0, $totalExams - $publishedExams)],
                        'colors' => ['#10B981', '#F59E0B'],
                    ],
                ];
                break;

            case 'marks':
                $query = Mark::with(['student.user', 'student.program', 'subject', 'exam'])
                    ->when($examId, fn ($q) => $q->where('exam_id', $examId))
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('semester', $semester))
                    ->when($search, function ($q) use ($search) {
                        $q->whereHas('student.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('subject', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                    });

                $data = $query->latest('id')->paginate(25)->withQueryString();

                $totalMarks = Mark::count();
                $absentMarks = Mark::where('is_absent', true)->count();
                $presentMarks = max(0, $totalMarks - $absentMarks);
                $kpis = [
                    ['label' => 'Total Mark Entries', 'value' => number_format($totalMarks)],
                    ['label' => 'Evaluated Students', 'value' => number_format($presentMarks)],
                    ['label' => 'Recorded Absentees', 'value' => number_format($absentMarks)],
                    ['label' => 'Filtered Marks', 'value' => number_format($data->total())],
                ];

                $theorySql = '(COALESCE(internal_theory_marks, 0) + COALESCE(external_theory_marks, 0))';
                $pracSql = '(COALESCE(internal_practical_marks, 0) + COALESCE(external_practical_marks, 0))';
                $totalSql = "({$theorySql} + {$pracSql} + COALESCE(assessment_obtained_marks, 0))";

                $distinction = Mark::where('is_absent', false)->whereRaw("{$totalSql} >= 80")->count();
                $firstDiv = Mark::where('is_absent', false)->whereRaw("{$totalSql} >= 60 AND {$totalSql} < 80")->count();
                $secondDiv = Mark::where('is_absent', false)->whereRaw("{$totalSql} >= 40 AND {$totalSql} < 60")->count();
                $fail = Mark::where('is_absent', false)->whereRaw("{$totalSql} < 40")->count();
                $charts = [
                    'chart1' => [
                        'title' => 'Evaluation Ratio (Evaluated vs Absent)',
                        'type' => 'doughnut',
                        'labels' => ['Evaluated Entries', 'Recorded Absentees'],
                        'data' => [$presentMarks, $absentMarks],
                        'colors' => ['#10B981', '#EF4444'],
                    ],
                    'chart2' => [
                        'title' => 'Score Brackets Distribution',
                        'type' => 'bar',
                        'labels' => ['Distinction (80%+)', 'First Div (60–79%)', 'Second Div (40–59%)', 'Below 40%'],
                        'data' => [$distinction, $firstDiv, $secondDiv, $fail],
                        'colors' => ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
                    ],
                ];
                break;

            case 'subjects':
                $query = Subject::with(['program.department', 'teachers.user'])
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('semester', $semester))
                    ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));

                $data = $query->orderBy('semester')->orderBy('name')->paginate(25)->withQueryString();

                $totalSubjects = Subject::count();
                $practicalSubjects = Subject::whereIn('type', ['practical', 'both'])->count();
                $kpis = [
                    ['label' => 'Total Subjects', 'value' => number_format($totalSubjects)],
                    ['label' => 'Practical / Lab Courses', 'value' => number_format($practicalSubjects)],
                    ['label' => 'Filtered Subjects', 'value' => number_format($data->total())],
                ];

                $theoryCount = Subject::where('type', 'theory')->count();
                $practicalCount = Subject::where('type', 'practical')->count();
                $bothCount = Subject::whereIn('type', ['both', 'theory_practical'])->count();
                $subSemLabels = [];
                $subSemCounts = [];
                for ($s = 1; $s <= 6; $s++) {
                    $subSemLabels[] = 'Sem ' . $s;
                    $subSemCounts[] = Subject::where('semester', $s)->count();
                }
                $charts = [
                    'chart1' => [
                        'title' => 'Curriculum Course Types Breakdown',
                        'type' => 'doughnut',
                        'labels' => ['Theory Courses', 'Practical / Labs', 'Both (Theory + Lab)'],
                        'data' => [$theoryCount, $practicalCount, $bothCount],
                        'colors' => ['#3B82F6', '#8B5CF6', '#10B981'],
                    ],
                    'chart2' => [
                        'title' => 'Course Allocation by Semester (Sem 1–6)',
                        'type' => 'bar',
                        'labels' => $subSemLabels,
                        'data' => $subSemCounts,
                        'colors' => ['#8B0000', '#B91C1C', '#DC2626', '#EA580C', '#F97316', '#FBBF24'],
                    ],
                ];
                break;

            case 'attendance':
            default:
                $type = 'attendance';
                $query = Student::with(['user', 'program', 'department'])
                    ->withCount([
                        'attendances as total_sessions',
                        'attendances as present_sessions' => fn ($q) => $q->where('status', 'present'),
                        'attendances as absent_sessions' => fn ($q) => $q->where('status', 'absent'),
                        'attendances as late_sessions' => fn ($q) => $q->where('status', 'late'),
                        'attendances as excused_sessions' => fn ($q) => $q->where('status', 'excused'),
                    ])
                    ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('current_semester', $semester))
                    ->when($academicSessionId, fn ($q) => $q->where('academic_session_id', $academicSessionId))
                    ->when($search, function ($q) use ($search) {
                        $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                          ->orWhere('student_no', 'like', "%{$search}%")
                          ->orWhere('roll_number', 'like', "%{$search}%");
                    });

                $data = $query->paginate(25)->withQueryString();

                $data->getCollection()->transform(function ($student) {
                    $student->attendance_rate = $student->total_sessions > 0
                        ? round(($student->present_sessions / $student->total_sessions) * 100, 1)
                        : 0;
                    return $student;
                });

                $totalSessions = AttendanceSession::count();
                $totalRecords = Attendance::count();
                $presentRecords = Attendance::where('status', 'present')->count();
                $overallRate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 1) : 0;

                $kpis = [
                    ['label' => 'Overall Attendance Rate', 'value' => $overallRate . '%'],
                    ['label' => 'Total Sessions Held', 'value' => number_format($totalSessions)],
                    ['label' => 'Total Present Marks', 'value' => number_format($presentRecords)],
                    ['label' => 'Filtered Students', 'value' => number_format($data->total())],
                ];

                $presentCount = Attendance::where('status', 'present')->count();
                $absentCount = Attendance::where('status', 'absent')->count();
                $lateCount = Attendance::where('status', 'late')->count();
                $excusedCount = Attendance::where('status', 'excused')->count();
                $semLabels = [];
                $semRates = [];
                for ($s = 1; $s <= 6; $s++) {
                    $semLabels[] = 'Sem ' . $s;
                    $tot = Attendance::whereHas('student', fn ($sq) => $sq->where('current_semester', $s))->count();
                    $pres = Attendance::where('status', 'present')->whereHas('student', fn ($sq) => $sq->where('current_semester', $s))->count();
                    $semRates[] = $tot > 0 ? round(($pres / $tot) * 100, 1) : 0;
                }
                $charts = [
                    'chart1' => [
                        'title' => 'Overall Attendance Status Breakdown',
                        'type' => 'doughnut',
                        'labels' => ['Present', 'Absent', 'Late', 'Excused'],
                        'data' => [$presentCount, $absentCount, $lateCount, $excusedCount],
                        'colors' => ['#10B981', '#EF4444', '#F59E0B', '#6366F1'],
                    ],
                    'chart2' => [
                        'title' => 'Average Attendance Rate by Semester (Sem 1–6)',
                        'type' => 'bar',
                        'labels' => $semLabels,
                        'data' => $semRates,
                        'colors' => ['#8B0000', '#B91C1C', '#DC2626', '#EA580C', '#F97316', '#FBBF24'],
                    ],
                ];
                break;
        }

        return view('admin.reports.index', compact(
            'type', 'departments', 'programs', 'academicSessions', 'exams',
            'departmentId', 'programId', 'semester', 'academicSessionId', 'examId', 'search',
            'data', 'kpis', 'charts'
        ));
    }

    /**
     * Export reports directly to formatted CSV / Excel with UTF-8 BOM.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->get('type', 'attendance');
        $departmentId = $request->get('department_id');
        $programId = $request->get('program_id');
        $semester = $request->get('semester');
        $academicSessionId = $request->get('academic_session_id');
        $examId = $request->get('exam_id');
        $search = trim((string) $request->get('search'));

        $filename = "mmp-{$type}-report-" . date('Y-m-d-His') . ".csv";

        return response()->streamDownload(function () use ($type, $departmentId, $programId, $semester, $academicSessionId, $examId, $search) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Institutional Title Header
            fputcsv($handle, ['MANMOHAN MEMORIAL POLYTECHNIC']);
            fputcsv($handle, ['OFFICIAL INSTITUTIONAL ' . strtoupper($type) . ' REPORT']);
            fputcsv($handle, ['Generated At:', now()->toDayDateTimeString(), 'Exported By:', auth()->user()->name ?? 'Administrator']);
            fputcsv($handle, []); // Blank separator row

            switch ($type) {
                case 'students':
                    fputcsv($handle, ['S.N.', 'Roll No', 'Registration No', 'Student Name', 'Email', 'Department', 'Program', 'Semester', 'Status']);
                    $students = Student::with(['user', 'department', 'program'])
                        ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                        ->when($programId, fn ($q) => $q->where('program_id', $programId))
                        ->when($semester, fn ($q) => $q->where('current_semester', $semester))
                        ->get();

                    foreach ($students as $i => $s) {
                        fputcsv($handle, [
                            $i + 1,
                            $s->roll_number ?? '—',
                            $s->registration_number ?? '—',
                            $s->user?->name ?? '—',
                            $s->user?->email ?? '—',
                            $s->department?->name ?? '—',
                            $s->program?->name ?? '—',
                            'Semester ' . $s->current_semester,
                            ucfirst($s->status ?? 'active'),
                        ]);
                    }
                    break;

                case 'exams':
                    fputcsv($handle, ['S.N.', 'Exam Title', 'Session', 'Department', 'Type', 'Full Marks', 'Pass Marks', 'Status', 'Published']);
                    $exams = Exam::with(['academicSession', 'department'])
                        ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                        ->get();

                    foreach ($exams as $i => $e) {
                        fputcsv($handle, [
                            $i + 1,
                            $e->name,
                            $e->academicSession?->name ?? 'Current',
                            $e->department?->name ?? 'All Departments',
                            ucfirst($e->exam_type ?? $e->type ?? 'General'),
                            $e->assessment_full_marks ?? 100,
                            $e->assessment_pass_marks ?? 40,
                            ucfirst($e->status ?? 'Draft'),
                            $e->is_published ? 'Yes' : 'No',
                        ]);
                    }
                    break;

                case 'marks':
                    fputcsv($handle, ['S.N.', 'Student Name', 'Roll No', 'Subject', 'Exam', 'Semester', 'Int Theory', 'Ext Theory', 'Int Practical', 'Ext Practical', 'Absent', 'Remarks']);
                    $marks = Mark::with(['student.user', 'subject', 'exam'])
                        ->when($examId, fn ($q) => $q->where('exam_id', $examId))
                        ->when($programId, fn ($q) => $q->where('program_id', $programId))
                        ->when($semester, fn ($q) => $q->where('semester', $semester))
                        ->get();

                    foreach ($marks as $i => $m) {
                        fputcsv($handle, [
                            $i + 1,
                            $m->student?->user?->name ?? '—',
                            $m->student?->roll_number ?? '—',
                            $m->subject?->name ?? '—',
                            $m->exam?->name ?? '—',
                            'Semester ' . $m->semester,
                            $m->internal_theory_marks ?? '0',
                            $m->external_theory_marks ?? '0',
                            $m->internal_practical_marks ?? '0',
                            $m->external_practical_marks ?? '0',
                            $m->is_absent ? 'YES' : 'NO',
                            $m->remarks ?? '—',
                        ]);
                    }
                    break;

                case 'subjects':
                    fputcsv($handle, ['S.N.', 'Subject Code', 'Subject Name', 'Department', 'Program', 'Semester', 'Type', 'Credit Hours', 'Internal Full', 'External Full']);
                    $subjects = Subject::with(['program.department'])
                        ->when($programId, fn ($q) => $q->where('program_id', $programId))
                        ->when($semester, fn ($q) => $q->where('semester', $semester))
                        ->get();

                    foreach ($subjects as $i => $sub) {
                        fputcsv($handle, [
                            $i + 1,
                            $sub->code ?? '—',
                            $sub->name,
                            $sub->program?->department?->name ?? '—',
                            $sub->program?->name ?? '—',
                            'Semester ' . $sub->semester,
                            ucfirst($sub->type ?? 'theory'),
                            $sub->credit_hours ?? 3,
                            ($sub->full_marks_internal_theory ?? 0) + ($sub->full_marks_internal_practical ?? 0),
                            ($sub->full_marks_external_theory ?? 0) + ($sub->full_marks_external_practical ?? 0),
                        ]);
                    }
                    break;

                case 'attendance':
                default:
                    fputcsv($handle, ['S.N.', 'Roll No', 'Student Name', 'Program', 'Department', 'Semester', 'Total Sessions', 'Present', 'Absent', 'Late', 'Excused', 'Attendance Rate (%)']);
                    $students = Student::with(['user', 'program', 'department'])
                        ->withCount([
                            'attendances as total_sessions',
                            'attendances as present_sessions' => fn ($q) => $q->where('status', 'present'),
                            'attendances as absent_sessions' => fn ($q) => $q->where('status', 'absent'),
                            'attendances as late_sessions' => fn ($q) => $q->where('status', 'late'),
                            'attendances as excused_sessions' => fn ($q) => $q->where('status', 'excused'),
                        ])
                        ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                        ->when($programId, fn ($q) => $q->where('program_id', $programId))
                        ->when($semester, fn ($q) => $q->where('current_semester', $semester))
                        ->get();

                    foreach ($students as $i => $s) {
                        $rate = $s->total_sessions > 0 ? round(($s->present_sessions / $s->total_sessions) * 100, 1) : 0;
                        fputcsv($handle, [
                            $i + 1,
                            $s->roll_number ?? '—',
                            $s->user?->name ?? '—',
                            $s->program?->name ?? '—',
                            $s->department?->name ?? '—',
                            'Semester ' . $s->current_semester,
                            $s->total_sessions,
                            $s->present_sessions,
                            $s->absent_sessions,
                            $s->late_sessions,
                            $s->excused_sessions,
                            $rate . '%',
                        ]);
                    }
                    break;
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Render the executive Print / PDF view with institutional header and verification signatures.
     */
    public function exportPrint(Request $request)
    {
        $type = $request->get('type', 'attendance');
        $departmentId = $request->get('department_id');
        $programId = $request->get('program_id');
        $semester = $request->get('semester');
        $academicSessionId = $request->get('academic_session_id');
        $examId = $request->get('exam_id');

        $department = $departmentId ? Department::find($departmentId) : null;
        $program = $programId ? Program::find($programId) : null;
        $session = $academicSessionId ? AcademicSession::find($academicSessionId) : (AcademicSession::current() ?? AcademicSession::where('is_active', true)->first());
        $exam = $examId ? Exam::find($examId) : null;

        $items = collect();

        switch ($type) {
            case 'students':
                $items = Student::with(['user', 'department', 'program'])
                    ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('current_semester', $semester))
                    ->orderBy('roll_number')
                    ->get();
                break;

            case 'exams':
                $items = Exam::with(['academicSession', 'department', 'programs'])
                    ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                    ->latest('id')
                    ->get();
                break;

            case 'marks':
                $items = Mark::with(['student.user', 'subject', 'exam'])
                    ->when($examId, fn ($q) => $q->where('exam_id', $examId))
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('semester', $semester))
                    ->get();
                break;

            case 'subjects':
                $items = Subject::with(['program.department', 'teachers.user'])
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('semester', $semester))
                    ->orderBy('semester')
                    ->orderBy('name')
                    ->get();
                break;

            case 'attendance':
            default:
                $type = 'attendance';
                $items = Student::with(['user', 'program', 'department'])
                    ->withCount([
                        'attendances as total_sessions',
                        'attendances as present_sessions' => fn ($q) => $q->where('status', 'present'),
                        'attendances as absent_sessions' => fn ($q) => $q->where('status', 'absent'),
                        'attendances as late_sessions' => fn ($q) => $q->where('status', 'late'),
                    ])
                    ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                    ->when($programId, fn ($q) => $q->where('program_id', $programId))
                    ->when($semester, fn ($q) => $q->where('current_semester', $semester))
                    ->orderBy('roll_number')
                    ->get();

                $items->transform(function ($student) {
                    $student->attendance_rate = $student->total_sessions > 0
                        ? round(($student->present_sessions / $student->total_sessions) * 100, 1)
                        : 0;
                    return $student;
                });
                break;
        }

        return view('admin.reports.print', compact(
            'type', 'items', 'department', 'program', 'session', 'exam', 'semester'
        ));
    }
}
