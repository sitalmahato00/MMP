<?php

namespace App\Modules\Teacher\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Department\Models\Department;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClassesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get teacher's subjects for current session with student counts
        $query = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with(['program:id,name,department_id', 'program.department:id,name']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $subjects = $query->get()->map(function ($subject) use ($teacher) {
            // Get student count for this subject
            $studentCount = \App\Models\Student::where('program_id', $subject->program_id)
                ->where('current_semester', $subject->semester)
                ->where('status', 'active')
                ->count();
            
            $subject->student_count = $studentCount;
            $subject->section_taught = $subject->pivot->section ?? 'All';
            
            return $subject;
        });

        // Get programs for filter
        $programs = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get()
            ->pluck('program')
            ->unique('id');

        // Get unique semesters
        $semesters = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->pluck('semester')
            ->unique()
            ->sort()
            ->values();

        return view('teacher.classes.index', compact('subjects', 'programs', 'semesters', 'session'));
    }

    public function show(Subject $subject, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher teaches this subject
        if (!$teacher->subjects()->where('subject_id', $subject->id)->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        $subject->load(['program:id,name,department_id', 'program.department:id,name']);

        // Get students enrolled in this subject's program and semester
        $query = \App\Models\Student::where('program_id', $subject->program_id)
            ->where('current_semester', $subject->semester)
            ->with([
                'user:id,name,email,avatar',
                'attendances' => function ($q) use ($teacher) {
                    $q->whereHas('attendanceSession', function ($sq) use ($teacher) {
                        $sq->where('teacher_id', $teacher->id);
                    });
                }
            ])
            ->withCount([
                'attendances as present_count' => function ($q) use ($teacher) {
                    $q->where('status', 'present')
                      ->whereHas('attendanceSession', fn ($sq) => $sq->where('teacher_id', $teacher->id));
                },
                'attendances as total_attendance' => function ($q) use ($teacher) {
                    $q->whereHas('attendanceSession', fn ($sq) => $sq->where('teacher_id', $teacher->id));
                }
            ]);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('student_no', 'like', '%' . $request->search . '%')
                  ->orWhere('roll_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($uq) use ($request) {
                      $uq->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->latest('id')->paginate(20)->withQueryString();

        // Get sections for filter
        $sections = \App\Models\Student::where('program_id', $subject->program_id)
            ->where('current_semester', $subject->semester)
            ->whereNotNull('section')
            ->distinct()
            ->pluck('section')
            ->sort()
            ->values();

        // Get statistics
        $totalStudents = \App\Models\Student::where('program_id', $subject->program_id)
            ->where('current_semester', $subject->semester)
            ->count();
        
        $activeStudents = \App\Models\Student::where('program_id', $subject->program_id)
            ->where('current_semester', $subject->semester)
            ->where('status', 'active')
            ->count();

        return view('teacher.classes.show', compact('subject', 'students', 'sections', 'totalStudents', 'activeStudents', 'session'));
    }
}
