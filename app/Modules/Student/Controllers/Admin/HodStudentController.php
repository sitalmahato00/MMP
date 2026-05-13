<?php

namespace App\Modules\Student\Controllers\Admin;


/**
 * HOD student management (department-scoped).
 *
 * Mirrors {@see \App\Http\Controllers\Admin\StudentController} but strictly
 * scoped to the HOD's department. Additionally, HODs may assign the
 * `roll_number` (which is reserved for HODs and not admin-fillable on the
 * Student model).
 */
use App\Helpers\NepaliDateHelper;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Exam;
use App\Modules\HOD\Controllers\HodController;
use App\Modules\Parent\Models\ParentModel;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use App\Traits\ExportableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HodStudentController extends HodController
{
    use ExportableTrait;
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId     = $department->id;

        $query = Student::query()
            ->where('department_id', $deptId)
            ->with([
                'user:id,name,email,avatar',
                'program:id,name',
                'academicSession:id,name',
            ])
            ->withCount('parents')
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                        ->orWhere('roll_number', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->program_id,          fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->semester,            fn ($q) => $q->where('current_semester', $request->semester))
            ->when($request->status,              fn ($q) => $q->where('status', $request->status));

        $students = (clone $query)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $currentSession = AcademicSession::current();

        $stats = Student::query()
            ->where('department_id', $deptId)
            ->selectRaw('COUNT(*) as total_students')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_students")
            ->selectRaw("SUM(CASE WHEN status = 'graduated' THEN 1 ELSE 0 END) as alumni_students")
            ->first();

        $totalStudents  = (int) ($stats->total_students  ?? 0);
        $activeStudents = (int) ($stats->active_students ?? 0);
        $alumniCount    = (int) ($stats->alumni_students ?? 0);

        $newThisSession = $currentSession
            ? Student::query()
                ->where('department_id', $deptId)
                ->where('academic_session_id', $currentSession->id)
                ->count()
            : 0;

        $programs = Program::where('department_id', $deptId)->orderBy('name')->get(['id', 'name']);
        $sessions = AcademicSession::orderByDesc('id')->limit(10)->get(['id', 'name', 'name_bs']);

        return view('hod.students.index', compact(
            'students', 'programs', 'sessions', 'department',
            'totalStudents', 'activeStudents', 'newThisSession', 'alumniCount'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $department     = $this->currentDepartment($request);
        $programs       = Program::where('department_id', $department->id)->orderBy('name')->get();
        $currentSession = AcademicSession::current();

        return view('hod.students.create', compact('programs', 'currentSession', 'department'));
    }

    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId     = $department->id;

        $data = $request->validate([
            // Personal
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'phone'               => 'nullable|string|max:20',
            'gender'              => 'nullable|in:male,female,other',
            'dob'                 => 'nullable|string|max:10',
            'address'             => 'nullable|string',
            'avatar'              => 'nullable|image|max:2048',
            'password'            => 'required|string|min:8',
            // Enrollment
            'student_no'          => 'required|string|max:50|unique:students,student_no',
            'roll_number'         => 'nullable|string|max:20',
            'registration_number' => 'nullable|string|max:50',
            'program_id'          => [
                'required',
                Rule::exists('programs', 'id')->where(fn ($q) => $q->where('department_id', $deptId)),
            ],
            'current_semester'    => 'required|integer|min:1|max:6',
            'section'             => 'nullable|string|max:10',
            'batch'               => 'nullable|string|max:20',
            'admission_date'      => 'nullable|string|max:10',
            'status'              => 'nullable|in:active,inactive,suspended',
            // Health & Emergency
            'blood_group'         => 'nullable|in:A+,A-,B+,B-,O+,O-,AB+,AB-',
            'guardian_name'       => 'nullable|string|max:255',
            'guardian_phone'      => 'nullable|string|max:20',
            // Parent auto-create
            'create_parent'       => 'nullable|boolean',
            'parent_name'         => 'required_if:create_parent,1|nullable|string|max:255',
            'parent_email'        => 'required_if:create_parent,1|nullable|email|unique:users,email',
            'parent_phone'        => 'nullable|string|max:20',
            'parent_relation'     => 'nullable|string|max:50',
            'parent_occupation'   => 'nullable|string|max:100',
        ]);

        $academicSession = AcademicSession::current();
        abort_if(!$academicSession, 422, 'Set an active academic session before enrolling students.');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $createdAccounts = [];

        DB::transaction(function () use ($data, $deptId, $academicSession, $request, &$createdAccounts) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone']   ?? null,
                'gender'    => $data['gender']  ?? null,
                'dob'       => NepaliDateHelper::toAD($data['dob'] ?? null),
                'address'   => $data['address'] ?? null,
                'avatar'    => $data['avatar']  ?? null,
                'password'  => Hash::make($data['password']),
                'is_active' => true,
            ]);
            $user->assignRole('student');
            $createdAccounts[] = ['user' => $user, 'password' => $data['password']];

            $student = new Student([
                'user_id'             => $user->id,
                'department_id'       => $deptId,
                'program_id'          => $data['program_id'],
                'academic_session_id' => $academicSession->id,
                'student_no'          => $data['student_no'],
                'registration_number' => $data['registration_number'] ?? null,
                'current_semester'    => $data['current_semester'],
                'section'             => $data['section']        ?? null,
                'batch'               => $data['batch']          ?? null,
                'admission_date'      => NepaliDateHelper::toAD($data['admission_date'] ?? null),
                'guardian_name'       => $data['guardian_name']  ?? null,
                'guardian_phone'      => $data['guardian_phone'] ?? null,
                'blood_group'         => $data['blood_group']    ?? null,
                'status'              => $data['status']         ?? 'active',
                'is_archived'         => false,
            ]);
            // roll_number is HOD-only and not mass-fillable
            if (!empty($data['roll_number'])) {
                $student->roll_number = $data['roll_number'];
            }
            $student->save();

            if ($request->boolean('create_parent') && !empty($data['parent_name']) && !empty($data['parent_email'])) {
                $parentUser = User::create([
                    'name'      => $data['parent_name'],
                    'email'     => $data['parent_email'],
                    'phone'     => $data['parent_phone'] ?? null,
                    'password'  => Hash::make($data['password']),
                    'is_active' => true,
                ]);
                $parentUser->assignRole('parent');
                $createdAccounts[] = ['user' => $parentUser, 'password' => $data['password']];

                $parentModel = ParentModel::create([
                    'user_id'             => $parentUser->id,
                    'occupation'          => $data['parent_occupation'] ?? null,
                    'relation_to_student' => $data['parent_relation']   ?? 'parent',
                ]);

                $student->parents()->attach($parentModel->id);
            }
        });

        $notificationService = app(\App\Services\PortalNotificationService::class);
        foreach ($createdAccounts as $account) {
            $notificationService->sendNewAccountCredentials($account['user'], $account['password'], auth()->user());
        }

        return redirect()
            ->route('hod.students.index')
            ->with('success', 'Student enrolled successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(Request $request, Student $student)
    {
        $this->authorizeDepartment($request, $student);

        $student->load([
            'user',
            'program',
            'department',
            'academicSession',
            'parents.user',
        ]);

        // Attendance stats (all time)
        $attendanceRecords = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->where('attendances.student_id', $student->id)
            ->select('attendances.*', 'attendance_sessions.date')
            ->get();

        $totalClasses = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $attendanceRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 1) : 0;

        // Monthly attendance for chart (last 6 months)
        $monthlyAttendance = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->select(
                DB::raw('DATE_FORMAT(attendance_sessions.date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) as present')
            )
            ->where('attendances.student_id', $student->id)
            ->where('attendance_sessions.date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($row) {
                $date = \Carbon\Carbon::parse($row->month . '-01');
                return [
                    'label' => bsDate($date, 'F Y'),
                    'present' => (int) $row->present,
                    'absent' => (int) ($row->total - $row->present),
                    'total' => (int) $row->total,
                ];
            });

        // Marks/Exams data
        $marks = DB::table('marks')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->join('subjects', 'marks.subject_id', '=', 'subjects.id')
            ->leftJoin('exam_program', function($join) use ($student) {
                $join->on('exams.id', '=', 'exam_program.exam_id')
                     ->where('exam_program.program_id', '=', $student->program_id);
            })
            ->where('marks.student_id', $student->id)
            ->select(
                'marks.*',
                'exams.name as exam_name',
                'exams.type as exam_type',
                'exam_program.semester',
                'subjects.name as subject_name',
                'subjects.code as subject_code'
            )
            ->orderByDesc('exams.start_date')
            ->get();

        $examsBySemester = $marks->groupBy('semester');

        // Assignments
        $assignments = DB::table('assignments')
            ->join('subjects', 'assignments.subject_id', '=', 'subjects.id')
            ->leftJoin('assignment_submissions', function ($join) use ($student) {
                $join->on('assignments.id', '=', 'assignment_submissions.assignment_id')
                    ->where('assignment_submissions.student_id', '=', $student->id);
            })
            ->where('assignments.program_id', $student->program_id)
            ->where('assignments.semester', $student->current_semester)
            ->select(
                'assignments.*',
                'subjects.name as subject_name',
                'assignment_submissions.id as submission_id',
                'assignment_submissions.status as submission_status',
                'assignment_submissions.created_at as submission_date',
                'assignment_submissions.marks_obtained as obtained_marks'
            )
            ->orderByDesc('assignments.due_date')
            ->get();

        // Timeline/Activity log
        $timeline = DB::table('audit_logs')
            ->where('model_type', 'App\\Models\\Student')
            ->where('model_id', $student->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('hod.students.show', compact(
            'student',
            'attendanceRate',
            'totalClasses',
            'presentCount',
            'monthlyAttendance',
            'marks',
            'examsBySemester',
            'assignments',
            'timeline'
        ));
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Request $request, Student $student)
    {
        $this->authorizeDepartment($request, $student);

        $department = $this->currentDepartment($request);
        $programs   = Program::where('department_id', $department->id)->orderBy('name')->get();

        return view('hod.students.edit', compact('student', 'programs', 'department'));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorizeDepartment($request, $student);
        $deptId = (int) $request->input('department_id');

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => ['required', 'email', Rule::unique('users')->ignore($student->user_id)],
            'phone'               => 'nullable|string|max:20',
            'gender'              => 'nullable|in:male,female,other',
            'dob'                 => 'nullable|string|max:10',
            'address'             => 'nullable|string',
            'avatar'              => 'nullable|image|max:2048',
            'student_no'          => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student->id)],
            'roll_number'         => ['nullable', 'string', 'max:20'],
            'registration_number' => 'nullable|string|max:50',
            'program_id'          => [
                'required',
                Rule::exists('programs', 'id')->where(fn ($q) => $q->where('department_id', $deptId)),
            ],
            'current_semester'    => 'required|integer|min:1|max:6',
            'section'             => 'nullable|string|max:10',
            'batch'               => 'nullable|string|max:20',
            'admission_date'      => 'nullable|string|max:10',
            'status'              => 'nullable|in:active,inactive,graduated,dropped,suspended',
            'blood_group'         => 'nullable|in:A+,A-,B+,B-,O+,O-,AB+,AB-',
            'guardian_name'       => 'nullable|string|max:255',
            'guardian_phone'      => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('avatar')) {
            if ($student->user->avatar && Storage::disk('public')->exists($student->user->avatar)) {
                Storage::disk('public')->delete($student->user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $student->user->update([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone']   ?? null,
            'gender'  => $data['gender']  ?? null,
            'dob'     => NepaliDateHelper::toAD($data['dob'] ?? null),
            'address' => $data['address'] ?? null,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        $student->fill([
            // department_id is NOT changeable from HOD (always stays in own dept)
            'program_id'          => $data['program_id'],
            'student_no'          => $data['student_no'],
            'registration_number' => $data['registration_number'] ?? null,
            'current_semester'    => $data['current_semester'],
            'section'             => $data['section']        ?? null,
            'batch'               => $data['batch']          ?? null,
            'admission_date'      => NepaliDateHelper::toAD($data['admission_date'] ?? null),
            'guardian_name'       => $data['guardian_name']  ?? null,
            'guardian_phone'      => $data['guardian_phone'] ?? null,
            'blood_group'         => $data['blood_group']    ?? null,
            'status'              => $data['status']         ?? $student->status,
        ]);

        // roll_number is HOD-only (not in $fillable) — set directly
        if (array_key_exists('roll_number', $data)) {
            $student->roll_number = $data['roll_number'];
        }

        $student->save();

        return redirect()
            ->route('hod.students.index')
            ->with('success', 'Student updated successfully.');
    }

    // ── Drawer ─────────────────────────────────────────────────────────────
    public function drawer(Request $request, Student $student)
    {
        $this->authorizeDepartment($request, $student);

        $student->load([
            'user',
            'program',
            'department',
            'academicSession',
            'parents.user',
        ]);

        // Get some quick stats
        $attendanceRate = 0; // TODO: Calculate attendance rate
        $examRecords = 0;    // TODO: Count exam records
        $assignments = 0;    // TODO: Count assignments

        return view('hod.students.drawer', compact('student', 'attendanceRate', 'examRecords', 'assignments'));
    }
    public function destroy(Request $request, Student $student)
    {
        $this->authorizeDepartment($request, $student);

        if ($student->user && $student->user->avatar && Storage::disk('public')->exists($student->user->avatar)) {
            Storage::disk('public')->delete($student->user->avatar);
        }

        $student->user?->forceDelete();
        $student->forceDelete();

        return redirect()
            ->route('hod.students.index')
            ->with('success', 'Student deleted.');
    }

    // ── Export Students ────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;
        $format = $request->get('format', 'csv');

        // Get students with same filters as index
        $students = Student::where('department_id', $deptId)
            ->with([
                'user:id,name,email,avatar',
                'program:id,name',
                'academicSession:id,name',
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                        ->orWhere('roll_number', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn ($q) => $q->where('current_semester', $request->semester))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('roll_number')
            ->get();

        return $this->exportStudentsData($students, $department, $format);
    }
}
