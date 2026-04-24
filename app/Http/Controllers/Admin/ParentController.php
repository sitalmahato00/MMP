<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $query = ParentModel::with(['user', 'students.user', 'students.department', 'students.program'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('user', fn($u) => $u->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%"));
                });
            })
            ->when($request->department_id, function ($q) use ($request) {
                $q->whereHas('students', fn($sq) => $sq->where('department_id', $request->department_id));
            })
            ->when($request->program_id, function ($q) use ($request) {
                $q->whereHas('students', fn($sq) => $sq->where('program_id', $request->program_id));
            })
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->whereHas('user', fn($u) => $u->where('is_active', (bool) $request->status));
            })
            ->when($request->linked === 'linked', function ($q) {
                $q->has('students');
            })
            ->when($request->linked === 'unlinked', function ($q) {
                $q->doesntHave('students');
            });

        $parents = (clone $query)->latest()->paginate(20)->withQueryString();

        // KPIs
        $totalParents   = ParentModel::count();
        $linkedChildren = DB::table('parent_student')->count();
        $unlinkedParents = ParentModel::doesntHave('students')->count();
        $recentlyAdded  = ParentModel::where('created_at', '>=', now()->subDays(30))->count();

        $departments = Department::orderBy('name')->get();
        $programs    = Program::orderBy('name')->get();

        return view('admin.parents.index', compact(
            'parents', 'departments', 'programs',
            'totalParents', 'linkedChildren', 'unlinkedParents', 'recentlyAdded'
        ));
    }

    public function create()
    {
        $students = Student::with(['user', 'department', 'program'])
            ->where('status', 'active')
            ->get()
            ->sortBy(fn($s) => $s->user?->name);

        return view('admin.parents.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'avatar'            => 'nullable|image|max:2048',
            'password'          => 'required|string|min:8',
            'occupation'        => 'nullable|string|max:100',
            'relation_to_student' => 'nullable|string|max:50',
            'student_ids'       => 'nullable|array',
            'student_ids.*'     => 'exists:students,id',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $createdUser = null;

        DB::transaction(function () use ($data, &$createdUser) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'address'   => $data['address'] ?? null,
                'avatar'    => $data['avatar'] ?? null,
                'password'  => Hash::make($data['password']),
                'is_active' => true,
            ]);
            $user->assignRole('parent');
            $createdUser = $user;

            $parent = ParentModel::create([
                'user_id'             => $user->id,
                'occupation'          => $data['occupation'] ?? null,
                'relation_to_student' => $data['relation_to_student'] ?? 'parent',
            ]);

            if (!empty($data['student_ids'])) {
                $parent->students()->sync($data['student_ids']);
            }
        });

        if ($createdUser) {
            app(\App\Services\PortalNotificationService::class)
                ->sendNewAccountCredentials($createdUser, $data['password'], auth()->user());
        }

        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent account created successfully.');
    }

    public function show(ParentModel $parent)
    {
        $parent->load([
            'user',
            'students.user',
            'students.department',
            'students.program',
            'students.attendances',
            'students.marks',
        ]);

        // Compute per-child stats
        $childrenStats = $parent->students->map(function ($student) {
            $totalAttendance = $student->attendances->count();
            $presentCount    = $student->attendances->where('status', 'present')->count();
            $attendancePct   = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : null;

            $publishedMarks = $student->marks->where('status', 'published');
            $avgMarks = $publishedMarks->count() > 0
                ? round($publishedMarks->avg(fn($m) => ($m->theory ?? 0) + ($m->practical ?? 0)), 1)
                : null;

            return [
                'student'       => $student,
                'attendancePct' => $attendancePct,
                'avgMarks'      => $avgMarks,
                'totalMarks'    => $publishedMarks->count(),
            ];
        });

        $lastLogin = $parent->user?->last_login_at;

        return view('admin.parents.show', compact('parent', 'childrenStats', 'lastLogin'));
    }

    public function edit(ParentModel $parent)
    {
        $parent->load(['user', 'students']);

        $students = Student::with(['user', 'department', 'program'])
            ->where('status', 'active')
            ->get()
            ->sortBy(fn($s) => $s->user?->name);

        return view('admin.parents.edit', compact('parent', 'students'));
    }

    public function update(Request $request, ParentModel $parent)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => ['required', 'email', Rule::unique('users', 'email')->ignore($parent->user_id)],
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'avatar'            => 'nullable|image|max:2048',
            'occupation'        => 'nullable|string|max:100',
            'relation_to_student' => 'nullable|string|max:50',
            'student_ids'       => 'nullable|array',
            'student_ids.*'     => 'exists:students,id',
            'is_active'         => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            if ($parent->user?->avatar) {
                Storage::disk('public')->delete($parent->user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        DB::transaction(function () use ($data, $parent, $request) {
            $parent->user->update([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'address'   => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? $parent->user->is_active,
                ...($request->hasFile('avatar') ? ['avatar' => $data['avatar']] : []),
            ]);

            $parent->update([
                'occupation'          => $data['occupation'] ?? null,
                'relation_to_student' => $data['relation_to_student'] ?? 'parent',
            ]);

            $parent->students()->sync($data['student_ids'] ?? []);
        });

        return redirect()->route('admin.parents.show', $parent)
            ->with('success', 'Parent updated successfully.');
    }

    public function destroy(ParentModel $parent)
    {
        DB::transaction(function () use ($parent) {
            $parent->students()->detach();
            if ($parent->user?->avatar) {
                Storage::disk('public')->delete($parent->user->avatar);
            }
            $userName = $parent->user?->name;
            $parent->user?->delete();
            $parent->delete();
        });

        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent account deleted successfully.');
    }
}
