<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Mark;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Teacher::query()
            ->with(['user:id,name,email,avatar', 'department:id,name'])
            ->withCount('subjects')
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('employee_id', 'like', "%{$term}%")
                        ->orWhere('designation', 'like', "%{$term}%")
                        ->orWhereHas('user', fn($u) => $u
                            ->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->department_id,   fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->designation,     fn($q) => $q->where('designation', $request->designation))
            ->when($request->employment_type, fn($q) => $q->where('employment_type', $request->employment_type))
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->where('is_active', (bool) $request->status);
            })
            ->when($request->semester, function ($q) use ($request) {
                $q->whereHas('subjects', fn($sq) => $sq->where('semester', $request->semester));
            });

        $teachers = (clone $query)->latest()->paginate(20)->withQueryString();
        $teacherIds = $teachers->getCollection()->pluck('id')->all();

        $semesterMap = empty($teacherIds)
            ? collect()
            : DB::table('subject_teacher')
                ->join('subjects', 'subjects.id', '=', 'subject_teacher.subject_id')
                ->whereIn('subject_teacher.teacher_id', $teacherIds)
                ->select(
                    'subject_teacher.teacher_id',
DB::raw("GROUP_CONCAT(DISTINCT subjects.semester) as semester_list")
                )
                ->groupBy('subject_teacher.teacher_id')
                ->pluck('semester_list', 'teacher_id');

        $teachers->setCollection(
            $teachers->getCollection()->map(function (Teacher $teacher) use ($semesterMap) {
                $semesterCsv = (string) ($semesterMap[$teacher->id] ?? '');
                $semesterList = collect(explode(',', $semesterCsv))
                    ->filter(fn ($value) => $value !== '')
                    ->map(fn ($value) => (int) $value)
                    ->sort()
                    ->values();

                $teacher->setAttribute('semester_list', $semesterList);

                return $teacher;
            })
        );

        // KPIs
        $totalTeachers  = Teacher::count();
        $activeTeachers = Teacher::where('is_active', true)->count();
        $hodCount       = Teacher::where('designation', 'HOD')->count();
        $totalSubjects  = DB::table('subject_teacher')->distinct()->count('subject_id');
        $sessionsMonth  = DB::table('attendance_sessions')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->count();
        $avgSessions = $totalTeachers > 0 ? round($sessionsMonth / $totalTeachers, 1) : 0;

        $departments = Department::orderBy('name')->get();

        return view('admin.teachers.index', compact(
            'teachers', 'departments',
            'totalTeachers', 'activeTeachers', 'hodCount', 'totalSubjects', 'avgSessions'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.teachers.create', compact('departments'));
    }

    // ── Store ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'nullable|string|max:20',
            'gender'          => 'nullable|in:male,female,other',
            'dob'             => 'nullable|string|max:12',
            'address'         => 'nullable|string|max:500',
            'avatar'          => 'nullable|image|max:2048',
            'password'        => 'required|string|min:8|confirmed',
            'department_id'   => 'nullable|exists:departments,id',
            'employee_id'     => 'nullable|string|max:50|unique:teachers,employee_id',
            'designation'     => 'nullable|in:Teacher,HOD',
            'qualification'   => 'nullable|string|max:255',
            'specialization'  => 'nullable|string|max:255',
            'join_date'       => 'nullable|string|max:12',
            'employment_type' => 'nullable|in:permanent,contract,part-time',
        ]);

        DB::transaction(function () use ($data, $request) {
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'gender'    => $data['gender'] ?? null,
                'dob'       => NepaliDateHelper::toAD($data['dob'] ?? null),
                'address'   => $data['address'] ?? null,
                'avatar'    => $avatarPath,
                'password'  => Hash::make($data['password']),
                'is_active' => true,
            ]);
            
            // Assign role based on designation
            $designation = $data['designation'] ?? 'Teacher';
            if ($designation === 'HOD') {
                $user->assignRole('hod');
            } else {
                $user->assignRole('teacher');
            }

            Teacher::create([
                'user_id'         => $user->id,
                'department_id'   => $designation === 'HOD' ? null : $data['department_id'],
                'employee_id'     => $data['employee_id'] ?? null,
                'designation'     => $designation,
                'qualification'   => $data['qualification'] ?? null,
                'specialization'  => $data['specialization'] ?? null,
                'join_date'       => NepaliDateHelper::toAD($data['join_date'] ?? null),
                'employment_type' => $data['employment_type'] ?? 'permanent',
                'is_active'       => true,
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher added successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(Teacher $teacher)
    {
        $this->loadTeacherData($teacher);
        $stats = $this->computeTeacherStats($teacher, withTimeline: true);

        return view('admin.teachers.show', compact('teacher', 'stats'));
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Teacher $teacher)
    {
        $teacher->load('user', 'department');
        $departments = Department::orderBy('name')->get();
        return view('admin.teachers.edit', compact('teacher', 'departments'));
    }

    // ── Update ─────────────────────────────────────────────────────────────
    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', Rule::unique('users')->ignore($teacher->user_id)],
            'phone'           => 'nullable|string|max:20',
            'gender'          => 'nullable|in:male,female,other',
            'dob'             => 'nullable|string|max:12',
            'address'         => 'nullable|string|max:500',
            'avatar'          => 'nullable|image|max:2048',
            'department_id'   => 'nullable|exists:departments,id',
            'employee_id'     => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'employee_id')->ignore($teacher->id)],
            'designation'     => 'nullable|in:Teacher,HOD',
            'qualification'   => 'nullable|string|max:255',
            'specialization'  => 'nullable|string|max:255',
            'join_date'       => 'nullable|string|max:12',
            'employment_type' => 'nullable|in:permanent,contract,part-time',
            'is_active'       => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            if ($teacher->user->avatar && Storage::disk('public')->exists($teacher->user->avatar)) {
                Storage::disk('public')->delete($teacher->user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $teacher->user->update(array_filter([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => NepaliDateHelper::toAD($data['dob'] ?? null),
            'address' => $data['address'] ?? null,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []), fn($v) => $v !== null));

        $teacher->user->update([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
        ]);

        $designation = $data['designation'] ?? 'Teacher';
        $teacher->update([
            'department_id'   => $designation === 'HOD' ? null : $data['department_id'],
            'employee_id'     => $data['employee_id'] ?? null,
            'designation'     => $designation,
            'qualification'   => $data['qualification'] ?? null,
            'specialization'  => $data['specialization'] ?? null,
            'join_date'       => NepaliDateHelper::toAD($data['join_date'] ?? null),
            'employment_type' => $data['employment_type'] ?? 'permanent',
            'is_active'       => $request->boolean('is_active', true),
        ]);

        // Update role based on designation
        $teacher->user->syncRoles([]); // Remove all roles first
        if ($designation === 'HOD') {
            $teacher->user->assignRole('hod');
        } else {
            $teacher->user->assignRole('teacher');
        }

        AuditLog::log('teacher.updated', $teacher);

        return redirect()->route('admin.teachers.show', $teacher)->with('success', 'Teacher updated successfully.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────
    public function destroy(Teacher $teacher)
    {
        if ($teacher->user->avatar && Storage::disk('public')->exists($teacher->user->avatar)) {
            Storage::disk('public')->delete($teacher->user->avatar);
        }
        $teacher->user->delete();
        $teacher->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher removed.');
    }

    // ── Drawer ─────────────────────────────────────────────────────────────
    public function drawer(Teacher $teacher)
    {
        $this->loadTeacherData($teacher);
        $stats = $this->computeTeacherStats($teacher, withTimeline: false);

        return view('admin.teachers._drawer', compact('teacher', 'stats'));
    }

    // ── Bulk Actions ───────────────────────────────────────────────────────
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'    => ['required', 'array', 'min:1', 'max:200'],
            'ids.*'  => ['integer', 'exists:teachers,id'],
            'action' => ['required', 'in:activate,deactivate,set_hod,set_teacher'],
        ]);

        $count = 0;
        DB::transaction(function () use ($request, &$count) {
            $teachers = Teacher::whereIn('id', $request->ids)->get();
            foreach ($teachers as $teacher) {
                match ($request->action) {
                    'activate'        => $teacher->update(['is_active' => true]),
                    'deactivate'      => $teacher->update(['is_active' => false]),
                    'set_hod'         => $teacher->update(['designation' => 'HOD']),
                    'set_teacher'     => $teacher->update(['designation' => 'Teacher']),
                };
                AuditLog::log('teacher.bulk_' . $request->action, $teacher);
                $count++;
            }
        });

        return response()->json(['success' => true, 'message' => "{$count} teacher(s) updated."]);
    }

    // ── Private helpers ────────────────────────────────────────────────────
    private function loadTeacherData(Teacher $teacher): void
    {
        $teacher->load([
            'user',
            'department',
            'subjects.program',
            'attendanceSessions',
            'marks.subject',
            'timetableSlots.subject',
            'timetableSlots.timetable.program',
        ]);
    }

    private function computeTeacherStats(Teacher $teacher, bool $withTimeline = false): array
    {
        // Subjects grouped by semester (for display)
        $subjectsBySemester = $teacher->subjects
            ->groupBy(fn($s) => $s->semester)
            ->sortKeys()
            ->map(fn($subjects) => $subjects->map(fn($s) => [
                'name'    => $s->name,
                'code'    => $s->code,
                'program' => $s->program?->name ?? '—',
                'section' => $s->pivot?->section ?? null,
                'type'    => $s->type ?? null,
            ])->all())
            ->all();

        $semestersHandled = $teacher->subjects->pluck('semester')->unique()->sort()->values()->all();

        // Monthly attendance (last 6 months) — associative: 'May 2025' => count
        $monthlyAttendance = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt  = now()->subMonths($i);
            $key = bsDate($dt, 'F Y') ?: $dt->format('M Y');
            $monthlyAttendance[$key] = $teacher->attendanceSessions
                ->filter(fn($s) => $s->date && (bsDate($s->date, 'Y-m') ?: Carbon::parse($s->date)->format('Y-m')) === (bsDate($dt, 'Y-m') ?: $dt->format('Y-m')))
                ->count();
        }

        $totalSessionsConducted = $teacher->attendanceSessions->count();
        $monthSessionsConducted = $teacher->attendanceSessions
            ->filter(fn($s) => $s->date && (bsDate($s->date, 'Y-m') ?: Carbon::parse($s->date)->format('Y-m')) === (bsDate(now(), 'Y-m') ?: now()->format('Y-m')))
            ->count();

        // Performance keyed by subject name => pass_rate
        $performanceBySubject = $teacher->marks
            ->where('status', 'published')
            ->groupBy('subject_id')
            ->mapWithKeys(function ($marks) {
                $total  = $marks->count();
                $passed = $marks->filter(function ($m) {
                    if ($m->is_absent || $m->is_withheld) return false;
                    $pts = ($m->internal_theory_marks ?? 0) + ($m->external_theory_marks ?? 0)
                         + ($m->internal_practical_marks ?? 0) + ($m->external_practical_marks ?? 0);
                    return $pts >= 40;
                })->count();
                $name = $marks->first()->subject?->name ?? 'Unknown';
                return [$name => $total > 0 ? round(($passed / $total) * 100) : 0];
            });

        $avgPassRate = $performanceBySubject->isNotEmpty()
            ? round($performanceBySubject->avg())
            : 0;

        $timetableSlots = $teacher->timetableSlots->groupBy('day_of_week');

        $stats = compact(
            'subjectsBySemester', 'semestersHandled', 'monthlyAttendance',
            'totalSessionsConducted', 'monthSessionsConducted',
            'performanceBySubject', 'avgPassRate', 'timetableSlots'
        );

        if ($withTimeline) {
            $stats['timeline'] = AuditLog::where('model_type', Teacher::class)
                ->where('model_id', $teacher->id)
                ->with('user')
                ->latest()
                ->limit(20)
                ->get();
        }

        return $stats;
    }
}

