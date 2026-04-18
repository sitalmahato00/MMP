<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Program::with(['department', 'coordinator.user'])
            ->withCount(['subjects', 'students'])
            ->when($request->search, fn($q) => $q->where(fn($i) =>
                $i->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
            ))
            ->when($request->department_id,  fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->duration_years,  fn($q) => $q->where('duration_years', $request->duration_years))
            ->when($request->total_semesters, fn($q) => $q->where('total_semesters', $request->total_semesters))
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', (bool) $request->status)
            );

        $programs = (clone $query)->latest()->paginate(20)->withQueryString();

        // KPIs
        $totalPrograms  = Program::count();
        $activePrograms = Program::where('is_active', true)->count();
        $totalStudents  = Student::count();
        $totalSubjects  = Subject::count();
        $deptCount      = Program::distinct('department_id')->count('department_id');

        $departments = Department::orderBy('name')->get();

        return view('admin.programs.index', compact(
            'programs', 'departments',
            'totalPrograms', 'activePrograms', 'totalStudents', 'totalSubjects', 'deptCount'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $teachers    = Teacher::with('user')->where('is_active', true)->orderBy('id')->get();
        return view('admin.programs.create', compact('departments', 'teachers'));
    }

    // ── Store ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:20|unique:programs,code',
            'department_id'    => 'required|exists:departments,id',
            'duration_years'   => 'required|integer|min:1|max:6',
            'total_semesters'  => 'required|integer|min:1|max:12',
            'coordinator_id'   => 'nullable|exists:teachers,id',
            'ctevt_code'       => 'nullable|string|max:50',
            'affiliation_type' => 'nullable|string|max:50',
            'description'      => 'nullable|string',
            'eligibility'      => 'nullable|string',
            'syllabus'         => 'nullable|file|mimes:pdf|max:10240',
            'is_active'        => 'boolean',
        ]);

        if ($request->hasFile('syllabus')) {
            $data['syllabus'] = $request->file('syllabus')->store('programs/syllabi', 'public');
        }

        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        $program = Program::create($data);
        AuditLog::log('program.created', $program, null, ['name' => $program->name]);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "Program '{$program->name}' created successfully.");
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(Program $program)
    {
        $program->load([
            'department',
            'coordinator.user',
            'subjects'  => fn($q) => $q->orderBy('semester')->orderBy('name'),
            'students.user',
            'timetables',
        ]);

        $subjectsBySemester = $program->subjects->groupBy('semester');
        $totalStudents      = $program->students->count();
        $activeStudents     = $program->students->where('status', 'active')->count();

        // Pass rate from marks
        $allMarks = Mark::where('program_id', $program->id)->get();
        $passRate = $allMarks->isNotEmpty()
            ? (int) round($allMarks->where('status', 'pass')->count() / $allMarks->count() * 100)
            : 0;

        // Running semesters (distinct current semesters across enrolled students)
        $runningSemesters = $program->students->pluck('current_semester')->unique()->sort()->values();

        // Enrollment per semester
        $enrollmentBySemester = collect(range(1, $program->total_semesters))
            ->mapWithKeys(fn($s) => [$s => $program->students->where('current_semester', $s)->count()]);

        // Subject type breakdown
        $theoryCount    = $program->subjects->filter(fn($s) => in_array($s->type, ['theory', 'both']))->count();
        $practicalCount = $program->subjects->filter(fn($s) => in_array($s->type, ['practical', 'both']))->count();

        // Assigned teachers
        $teachers = Teacher::with([
            'user',
            'subjects' => fn($q) => $q->where('program_id', $program->id),
        ])->whereHas('subjects', fn($q) => $q->where('program_id', $program->id))->get();

        // Enrollment trend (by batch)
        $enrollmentTrend = $program->students
            ->groupBy(fn($s) => $s->batch ?? 'Unknown')
            ->map->count()
            ->sortKeys()
            ->take(6);

        // Subject completion & health score
        $subjectCompletionRate = $program->subjects->count() > 0
            ? (int) round($program->subjects->where('is_active', true)->count() / $program->subjects->count() * 100)
            : 0;
        $healthScore = $program->subjects->count()
            ? (int) round(($passRate + $subjectCompletionRate) / 2)
            : 0;

        // Credit hours per semester
        $creditsBySemester = $subjectsBySemester->map(fn($subs) => $subs->sum('credit_hours'));

        $stats = compact(
            'subjectsBySemester', 'totalStudents', 'activeStudents',
            'passRate', 'runningSemesters', 'enrollmentBySemester',
            'theoryCount', 'practicalCount', 'teachers',
            'enrollmentTrend', 'healthScore', 'subjectCompletionRate',
            'creditsBySemester'
        );

        $auditLogs = AuditLog::with('user')
            ->where('model_type', Program::class)
            ->where('model_id', $program->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.programs.show', compact('program', 'stats', 'auditLogs'));
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Program $program)
    {
        $departments = Department::orderBy('name')->get();
        $teachers    = Teacher::with('user')->where('is_active', true)->get();
        return view('admin.programs.edit', compact('program', 'departments', 'teachers'));
    }

    // ── Update ─────────────────────────────────────────────────────────────
    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'code'             => ['required', 'string', 'max:20', Rule::unique('programs', 'code')->ignore($program->id)],
            'department_id'    => 'required|exists:departments,id',
            'duration_years'   => 'required|integer|min:1|max:6',
            'total_semesters'  => 'required|integer|min:1|max:12',
            'coordinator_id'   => 'nullable|exists:teachers,id',
            'ctevt_code'       => 'nullable|string|max:50',
            'affiliation_type' => 'nullable|string|max:50',
            'description'      => 'nullable|string',
            'eligibility'      => 'nullable|string',
            'syllabus'         => 'nullable|file|mimes:pdf|max:10240',
            'is_active'        => 'boolean',
        ]);

        if ($request->hasFile('syllabus')) {
            if ($program->syllabus) {
                Storage::disk('public')->delete($program->syllabus);
            }
            $data['syllabus'] = $request->file('syllabus')->store('programs/syllabi', 'public');
        }

        $old = $program->only(['name', 'code', 'is_active']);
        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $program->update($data);
        AuditLog::log('program.updated', $program, $old, ['name' => $program->name]);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.programs.show', $program)
            ->with('success', 'Program updated successfully.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────
    public function destroy(Program $program)
    {
        if ($program->syllabus) {
            Storage::disk('public')->delete($program->syllabus);
        }
        AuditLog::log('program.deleted', $program, ['name' => $program->name]);
        $name = $program->name;
        $program->delete();
        PublicDataService::invalidate('*');

        return redirect()->route('admin.programs.index')
            ->with('success', "Program '{$name}' deleted.");
    }

    // ── Bulk Action ────────────────────────────────────────────────────────
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:programs,id',
            'action' => 'required|in:activate,deactivate,delete',
        ]);

        DB::transaction(function () use ($request) {
            $programs = Program::whereIn('id', $request->ids)->get();
            foreach ($programs as $program) {
                match ($request->action) {
                    'activate'   => $program->update(['is_active' => true]),
                    'deactivate' => $program->update(['is_active' => false]),
                    'delete'     => tap($program)->delete(),
                };
                AuditLog::log("program.bulk.{$request->action}", $program);
            }
        });

        PublicDataService::invalidate('*');
        return response()->json(['success' => true, 'count' => count($request->ids)]);
    }
}


class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $programs = Program::with('department')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->latest()
            ->paginate(20);

        $departments = Department::orderBy('name')->get();
        return view('admin.programs.index', compact('programs', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.programs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'department_id'    => 'required|exists:departments,id',
            'duration_years'   => 'required|integer|min:1|max:6',
            'total_semesters'  => 'required|integer|min:1|max:6',
        ]);

        Program::create($data);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.programs.index')
            ->with('success', "Program '{$data['name']}' created.");
    }

    public function edit(Program $program)
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.programs.edit', compact('program', 'departments'));
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'department_id'   => 'required|exists:departments,id',
            'duration_years'  => 'required|integer|min:1|max:6',
            'total_semesters' => 'required|integer|min:1|max:6',
        ]);

        $program->update($data);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program updated.');
    }

    public function destroy(Program $program)
    {
        $program->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.programs.index')
            ->with('success', 'Program deleted.');
    }
}
