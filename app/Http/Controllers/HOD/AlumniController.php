<?php

namespace App\Http\Controllers\HOD;

use App\Models\Student;
use App\Models\Alumni;
use App\Models\User;
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

        // Get graduating students (final semester students)
        $graduatingStudents = Student::where('department_id', $deptId)
            ->where('is_active', true)
            ->with(['user:id,name,email,phone', 'program:id,name,duration'])
            ->get()
            ->filter(function ($student) {
                // Check if student is in final semester based on program duration
                $programDuration = $student->program->duration ?? 4; // Default 4 semesters
                return $student->semester >= $programDuration;
            });

        // Get already prepared alumni
        $preparedAlumni = Alumni::whereHas('student', fn ($q) => $q->where('department_id', $deptId))
            ->with(['student.user:id,name,email', 'student.program:id,name'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        // Stats
        $totalGraduating = $graduatingStudents->count();
        $totalPrepared = Alumni::whereHas('student', fn ($q) => $q->where('department_id', $deptId))->count();
        $pendingPreparation = $totalGraduating - $totalPrepared;

        return view('hod.alumni.index', compact(
            'department', 'graduatingStudents', 'preparedAlumni',
            'totalGraduating', 'totalPrepared', 'pendingPreparation'
        ));
    }

    // ── Graduating Students ────────────────────────────────────────────────
    public function graduating(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get all final semester students
        $query = Student::where('department_id', $deptId)
            ->with(['user:id,name,email,phone', 'program:id,name,duration'])
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

        // Filter to show only final semester students
        $students->getCollection()->transform(function ($student) {
            $programDuration = $student->program->duration ?? 4;
            $student->is_graduating = $student->semester >= $programDuration;
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
        $programDuration = $student->program->duration ?? 4;
        if ($student->semester < $programDuration) {
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
                'student_id' => $student->id,
                'graduation_year' => now()->year,
                'graduation_date' => now(),
                'current_status' => 'recent_graduate',
                'is_active' => true,
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
                'achievements',
                'employments',
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
            $alumnus->achievements_count = $alumnus->achievements->count();
            $alumnus->employments_count = $alumnus->employments->count();
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