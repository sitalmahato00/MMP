<?php

namespace App\Http\Controllers\HOD;

use App\Models\Student;
use App\Models\Alumni;
use App\Models\User;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * HOD alumni preparation management (department-scoped).
 * 
 * HODs can prepare graduating students for alumni status.
 */
class AlumniController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get graduating students (only final semester) with pagination
        $graduatingStudentsQuery = Student::where('department_id', $deptId)
            ->where('is_active', true)
            ->with(['user:id,name,email,phone,avatar', 'program:id,name,total_semesters,duration_years'])
            ->whereHas('program', function ($q) {
                $q->whereRaw('students.current_semester >= programs.total_semesters');
            })
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id));

        $graduatingStudents = $graduatingStudentsQuery
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Get already prepared alumni with pagination
        $preparedAlumniQuery = Alumni::whereHas('student', fn ($q) => $q->where('department_id', $deptId))
            ->with(['student.user:id,name,email,avatar', 'student.program:id,name'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('student.user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            });

        $preparedAlumni = $preparedAlumniQuery
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        // Stats - count only final semester students
        $totalGraduating = Student::where('department_id', $deptId)
            ->where('is_active', true)
            ->whereHas('program', function ($q) {
                $q->whereRaw('students.current_semester >= programs.total_semesters');
            })
            ->count();
        $totalPrepared = Alumni::whereHas('student', fn ($q) => $q->where('department_id', $deptId))->count();
        $pendingPreparation = $totalGraduating - $totalPrepared;

        // Programs for filter
        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.alumni.index', compact(
            'department', 'graduatingStudents', 'preparedAlumni',
            'totalGraduating', 'totalPrepared', 'pendingPreparation', 'programs'
        ));
    }

    // ── Graduating Students ────────────────────────────────────────────────
    public function graduating(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get all final semester students
        $query = Student::where('department_id', $deptId)
            ->with(['user:id,name,email,phone,avatar', 'program:id,name,total_semesters,duration_years'])
            ->whereHas('program', function ($q) {
                $q->whereRaw('students.current_semester >= programs.total_semesters');
            })
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'ready') {
                    // Students ready for alumni preparation
                    $q->where('is_active', true)
                      ->whereDoesntHave('alumni');
                } elseif ($request->status === 'prepared') {
                    // Students already prepared as alumni
                    $q->whereHas('alumni');
                }
            });

        $students = (clone $query)
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Add alumni record flag
        $students->getCollection()->transform(function ($student) {
            $student->is_graduating = true; // All are graduating since we filtered
            $student->has_alumni_record = Alumni::where('student_id', $student->id)->exists();
            return $student;
        });

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.alumni.graduating', compact('students', 'department', 'programs'));
    }

    // ── Prepare Student for Alumni ─────────────────────────────────────────
    public function prepare(Request $request, Student $student)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify student belongs to department
        if ($student->department_id !== $deptId) {
            abort(403, 'Unauthorized access to student.');
        }

        // Check if student is in final semester
        $programTotalSemesters = $student->program->total_semesters ?? 6;
        if ($student->current_semester < $programTotalSemesters) {
            return redirect()->back()
                ->with('error', 'Student is not in final semester yet.');
        }

        // Check if already prepared
        if (Alumni::where('student_id', $student->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Student is already prepared as alumni.');
        }

        DB::transaction(function () use ($student) {
            // Create alumni record
            $alumni = Alumni::create([
                'user_id' => $student->user_id,
                'student_id' => $student->id,
                'department_id' => $student->department_id,
                'program_id' => $student->program_id,
                'roll_number' => $student->roll_number ?? null,
                'admission_year' => $student->admission_date ? date('Y', strtotime($student->admission_date)) : null,
                'graduation_year' => now()->year,
                'graduation_date' => now(),
                'current_status' => 'recent_graduate',
                'is_active' => true,
                'is_verified' => true,
            ]);

            // Assign alumni role to user
            $student->user->assignRole('alumni');

            // Deactivate student record
            $student->update(['is_active' => false]);
        });

        return redirect()->back()
            ->with('success', 'Student successfully prepared for alumni status.');
    }

    // ── Alumni Records ─────────────────────────────────────────────────────
    public function records(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get all alumni from department
        $query = Alumni::whereHas('student', fn ($q) => $q->where('department_id', $deptId))
            ->with([
                'student.user:id,name,email,phone',
                'student.program:id,name',
                'achievementRecords',
                'employmentHistory',
                'projects'
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('student.user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->graduation_year, fn ($q) => $q->where('graduation_year', $request->graduation_year))
            ->when($request->status, fn ($q) => $q->where('current_status', $request->status))
            ->when($request->program_id, function ($q) use ($request) {
                $q->whereHas('student', fn ($sq) => $sq->where('program_id', $request->program_id));
            });

        $alumni = (clone $query)
            ->latest('graduation_date')
            ->paginate(20)
            ->withQueryString();

        // Add statistics to each alumni
        $alumni->getCollection()->transform(function ($alumnus) {
            $alumnus->achievements_count = $alumnus->achievementRecords->count();
            $alumnus->employments_count = $alumnus->employmentHistory->count();
            $alumnus->projects_count = $alumnus->projects->count();
            return $alumnus;
        });

        // Stats
        $totalAlumni = (clone $query)->count();
        $recentGraduates = (clone $query)->where('graduation_year', now()->year)->count();
        $employedAlumni = (clone $query)->where('current_status', 'employed')->count();
        $entrepreneurAlumni = (clone $query)->where('current_status', 'entrepreneur')->count();

        // Graduation years for filter
        $graduationYears = Alumni::whereHas('student', fn ($q) => $q->where('department_id', $deptId))
            ->distinct()
            ->orderByDesc('graduation_year')
            ->pluck('graduation_year');

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.alumni.records', compact(
            'alumni', 'department', 'programs', 'graduationYears',
            'totalAlumni', 'recentGraduates', 'employedAlumni', 'entrepreneurAlumni'
        ));
    }
}