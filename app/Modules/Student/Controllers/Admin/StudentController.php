<?php

namespace App\Modules\Student\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Mark;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->with([
                'user:id,name,email,avatar',
                'program:id,name',
                'department:id,name',
                'academicSession:id,name',
            ])
            ->withCount('parents')
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->department_id,       fn ($q) => $q->where('department_id', $request->department_id))
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
            ->selectRaw('COUNT(*) as total_students')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_students")
            ->selectRaw("SUM(CASE WHEN status = 'graduated' THEN 1 ELSE 0 END) as alumni_students")
            ->first();

        $totalStudents  = (int) ($stats->total_students ?? 0);
        $activeStudents = (int) ($stats->active_students ?? 0);
        $alumniCount    = (int) ($stats->alumni_students ?? 0);
        $newThisSession = $currentSession
            ? Student::query()->where('academic_session_id', $currentSession->id)->count()
            : 0;

        $programs    = Program::query()->orderBy('name')->get(['id', 'name']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $sessions    = AcademicSession::query()->orderByDesc('id')->limit(10)->get(['id', 'name', 'name_bs']);

        return view('admin.students.index', compact(
            'students', 'programs', 'departments', 'sessions',
            'totalStudents', 'activeStudents', 'newThisSession', 'alumniCount'
        ));
    }

    public function create()
    {
        $programs       = Program::with('department')->orderBy('name')->get();
        $currentSession = AcademicSession::current();
        return view('admin.students.create', compact('programs', 'currentSession'));
    }

    public function store(Request $request)
    {
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
            'registration_number' => 'nullable|string|max:50',
            'program_id'          => 'required|exists:programs,id',
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

        $program         = Program::with('department')->findOrFail($data['program_id']);
        $academicSession = AcademicSession::current();

        abort_if(!$program->department_id, 422, 'Selected program must belong to a department before enrolling a student.');
        abort_if(!$academicSession,        422, 'Set an active academic session before enrolling students.');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $createdAccounts = [];

        DB::transaction(function () use ($data, $program, $academicSession, $request, &$createdAccounts) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'gender'    => $data['gender'] ?? null,
                'dob'       => NepaliDateHelper::toAD($data['dob'] ?? null),
                'address'   => $data['address'] ?? null,
                'avatar'    => $data['avatar'] ?? null,
                'password'  => Hash::make($data['password']),
                'is_active' => true,
            ]);
            $user->assignRole('student');
            $createdAccounts[] = ['user' => $user, 'password' => $data['password']];

            $student = Student::create([
                'user_id'             => $user->id,
                'department_id'       => $program->department_id,
                'program_id'          => $data['program_id'],
                'academic_session_id' => $academicSession->id,
                'student_no'          => $data['student_no'],
                'registration_number' => $data['registration_number'] ?? null,
                'current_semester'    => $data['current_semester'],
                'section'             => $data['section'] ?? null,
                'batch'               => $data['batch'] ?? null,
                'admission_date'      => NepaliDateHelper::toAD($data['admission_date'] ?? null),
                'guardian_name'       => $data['guardian_name'] ?? null,
                'guardian_phone'      => $data['guardian_phone'] ?? null,
                'blood_group'         => $data['blood_group'] ?? null,
                'status'              => $data['status'] ?? 'active',
                'is_archived'         => false,
            ]);

            // Auto-create parent portal account
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
                    'relation_to_student' => $data['parent_relation'] ?? 'parent',
                ]);

                $student->parents()->attach($parentModel->id);
            }
        });

        $notificationService = app(\App\Services\PortalNotificationService::class);
        foreach ($createdAccounts as $account) {
            $notificationService->sendNewAccountCredentials($account['user'], $account['password'], auth()->user());
        }

        return redirect()->route('admin.students.index')->with('success', 'Student enrolled successfully.');
    }

    public function show(Student $student)
    {
        $student->load([
            'user',
            'program.department',
            'department',
            'academicSession',
            'parents.user',
            'alumnus',
            'submissions.assignment.subject',
        ]);

        // Attendance — use DB counts to avoid loading all records
        $attendanceTotal   = Attendance::where('student_id', $student->id)->count();
        $attendancePresent = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attendancePct     = $attendanceTotal > 0
            ? round(($attendancePresent / $attendanceTotal) * 100, 1)
            : null;

        // Monthly chart: only last 8 months of attendance sessions (AD date filter)
        $monthlyAttendance = Attendance::with('attendanceSession:id,date')
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', fn ($q) => $q
                ->whereNotNull('date')
                ->where('date', '>=', now()->subMonths(8)->startOfMonth())
            )
            ->get()
            ->filter(fn ($a) => $a->attendanceSession?->date !== null)
            ->groupBy(fn ($a) => bsDate($a->attendanceSession->date, 'Y-m'))
            ->sortKeysDesc()
            ->take(6)
            ->sortKeys()
            ->map(fn ($group) => [
                'label'   => bsDate($group->first()->attendanceSession->date, 'F Y'),
                'present' => $group->where('status', 'present')->count(),
                'absent'  => $group->where('status', 'absent')->count(),
                'total'   => $group->count(),
            ])
            ->values();

        // Marks — per-semester lazy load (avoids loading all 300+ marks at once)
        $marksTotal   = Mark::where('student_id', $student->id)->where('status', 'published')->count();
        $allSemesters = Mark::where('student_id', $student->id)
            ->where('status', 'published')
            ->orderBy('semester')
            ->distinct()
            ->pluck('semester');

        $activeSem = (int) request()->integer('mark_sem', $student->current_semester);
        if ($allSemesters->isNotEmpty() && ! $allSemesters->contains($activeSem)) {
            $activeSem = $allSemesters->last();
        }

        $marksBySemester = Mark::with(['subject:id,name', 'exam:id,name'])
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->where('semester', $activeSem)
            ->get()
            ->groupBy('semester')
            ->map(fn ($group) => $group->groupBy(fn ($m) => $m->subject?->name ?? 'Unknown'));

        // Assignments
        $submissions = $student->submissions->sortByDesc('created_at')->take(20);

        // Timeline
        $timeline = AuditLog::where('model_type', Student::class)
            ->where('model_id', $student->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.students.show', compact(
            'student',
            'attendanceTotal', 'attendancePresent', 'attendancePct', 'monthlyAttendance',
            'marksTotal', 'allSemesters', 'activeSem', 'marksBySemester',
            'submissions',
            'timeline'
        ));
    }

    public function edit(Student $student)
    {
        $programs = Program::with('department')->orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'programs'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => ['required', 'email', Rule::unique('users')->ignore($student->user_id)],
            'phone'               => 'nullable|string|max:20',
            'gender'              => 'nullable|in:male,female,other',
            'dob'                 => 'nullable|string|max:10',
            'address'             => 'nullable|string',
            'avatar'              => 'nullable|image|max:2048',
            'student_no'          => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student->id)],
            'registration_number' => 'nullable|string|max:50',
            'program_id'          => 'required|exists:programs,id',
            'current_semester'    => 'required|integer|min:1|max:6',
            'section'             => 'nullable|string|max:10',
            'batch'               => 'nullable|string|max:20',
            'admission_date'      => 'nullable|string|max:10',
            'status'              => 'nullable|in:active,inactive,graduated,dropped,suspended',
            'blood_group'         => 'nullable|in:A+,A-,B+,B-,O+,O-,AB+,AB-',
            'guardian_name'       => 'nullable|string|max:255',
            'guardian_phone'      => 'nullable|string|max:20',
        ]);

        $program = Program::with('department')->findOrFail($data['program_id']);

        abort_if(!$program->department_id, 422, 'Selected program must belong to a department before saving this student.');

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
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => NepaliDateHelper::toAD($data['dob'] ?? null),
            'address' => $data['address'] ?? null,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        $student->update([
            'department_id'       => $program->department_id,
            'program_id'          => $data['program_id'],
            'student_no'          => $data['student_no'],
            'registration_number' => $data['registration_number'] ?? null,
            'current_semester'    => $data['current_semester'],
            'section'             => $data['section'] ?? null,
            'batch'               => $data['batch'] ?? null,
            'admission_date'      => NepaliDateHelper::toAD($data['admission_date'] ?? null),
            'guardian_name'       => $data['guardian_name'] ?? null,
            'guardian_phone'      => $data['guardian_phone'] ?? null,
            'blood_group'         => $data['blood_group'] ?? null,
            'status'              => $data['status'] ?? $student->status,
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        if ($student->user->avatar && Storage::disk('public')->exists($student->user->avatar)) {
            Storage::disk('public')->delete($student->user->avatar);
        }
        $student->user->delete();
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }

    // ── Bulk Promote ───────────────────────────────────────────────────────
    public function bulkPromote(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer', 'exists:students,id'],
        ]);

        $promoted  = 0;
        $skipped   = 0;
        $graduated = 0;

        DB::transaction(function () use ($request, &$promoted, &$skipped, &$graduated) {
            $students = Student::whereIn('id', $request->ids)->get();

            foreach ($students as $student) {
                if ($student->current_semester >= 6) {
                    // Already at final semester — graduate instead
                    if ($student->status !== 'graduated') {
                        $student->status = 'graduated';
                        $student->save();
                        $graduated++;

                        AuditLog::create([
                            'user_id'    => auth()->id(),
                            'action'     => 'student.graduated',
                            'model_type' => Student::class,
                            'model_id'   => $student->id,
                            'new_values' => json_encode(['status' => 'graduated']),
                            'ip_address' => $request->ip(),
                        ]);
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                $old = $student->current_semester;
                $student->current_semester = $old + 1;
                $student->save();
                $promoted++;

                AuditLog::create([
                    'user_id'    => auth()->id(),
                    'action'     => 'student.promoted',
                    'model_type' => Student::class,
                    'model_id'   => $student->id,
                    'old_values' => json_encode(['current_semester' => $old]),
                    'new_values' => json_encode(['current_semester' => $student->current_semester]),
                    'ip_address' => $request->ip(),
                ]);
            }
        });

        $parts = [];
        if ($promoted)  $parts[] = "{$promoted} promoted to next semester";
        if ($graduated) $parts[] = "{$graduated} marked as graduated";
        if ($skipped)   $parts[] = "{$skipped} already graduated (skipped)";

        return response()->json([
            'success'  => true,
            'message'  => implode(', ', $parts) . '.',
            'promoted' => $promoted,
            'graduated'=> $graduated,
            'skipped'  => $skipped,
        ]);
    }

    // ── Drawer ─────────────────────────────────────────────────────────────
    public function drawer(Student $student)
    {
        $student->load([
            'user',
            'program',
            'department',
            'academicSession',
            'parents.user',
            'marks.subject',
            'marks.exam',
            'attendances.attendanceSession',
            'submissions.assignment.subject',
        ]);

        // ── Attendance summary ─────────────────────────────────────────────
        $attendanceTotal   = $student->attendances->count();
        $attendancePresent = $student->attendances->where('status', 'present')->count();
        $attendancePct     = $attendanceTotal > 0
            ? round(($attendancePresent / $attendanceTotal) * 100, 1)
            : null;

        // Monthly breakdown (last 6 months)
        $monthlyAttendance = $student->attendances
            ->filter(fn ($a) => $a->attendanceSession?->date !== null)
            ->groupBy(fn ($a) => bsDate($a->attendanceSession->date, 'Y-m'))
            ->sortKeysDesc()
            ->take(6)
            ->sortKeys()
            ->map(fn ($group) => [
                'label'   => bsDate($group->first()->attendanceSession->date, 'F Y'),
                'present' => $group->where('status', 'present')->count(),
                'absent'  => $group->where('status', 'absent')->count(),
                'total'   => $group->count(),
            ])
            ->values();

        // ── Marks summary ──────────────────────────────────────────────────
        $marksBySemester = $student->marks
            ->where('status', 'published')
            ->groupBy('semester')
            ->sortKeys()
            ->map(fn ($group) => $group->groupBy(fn ($m) => $m->subject?->name ?? 'Unknown'));

        // ── Assignments ────────────────────────────────────────────────────
        $submissions = $student->submissions->sortByDesc('created_at')->take(20);

        // ── Timeline ──────────────────────────────────────────────────────
        $timeline = AuditLog::where('model_type', Student::class)
            ->where('model_id', $student->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.students._drawer', compact(
            'student',
            'attendanceTotal', 'attendancePresent', 'attendancePct', 'monthlyAttendance',
            'marksBySemester',
            'submissions',
            'timeline'
        ));
    }
}
