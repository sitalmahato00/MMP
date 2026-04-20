<?php

namespace App\Http\Controllers\HOD;

use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HOD timetable management (department-scoped).
 * 
 * HODs can manage timetables for their department programs only.
 */
class TimetableController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get timetables for department programs
        $query = Timetable::whereHas('program', fn ($q) => $q->where('department_id', $deptId))
            ->with([
                'academicSession:id,name',
                'program:id,name',
                'slots' => fn ($q) => $q->with(['subject:id,name', 'teacher.user:id,name'])
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$term}%"));
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn ($q) => $q->where('semester', $request->semester))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'active') {
                    $q->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $q->where('is_active', false);
                }
            });

        $timetables = (clone $query)
            ->latest('effective_from')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $totalTimetables = (clone $query)->count();
        $activeTimetables = (clone $query)->where('is_active', true)->count();
        $thisWeekSlots = TimetableSlot::whereHas('timetable', function ($q) use ($deptId) {
                $q->whereHas('program', fn ($pq) => $pq->where('department_id', $deptId))
                  ->where('is_active', true);
            })
            ->whereBetween('day_of_week', [1, 7])
            ->count();

        // Programs for filter
        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.timetable.index', compact(
            'timetables', 'department', 'programs',
            'totalTimetables', 'activeTimetables', 'thisWeekSlots'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $academicSessions = AcademicSession::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.timetable.create', compact('department', 'programs', 'academicSessions'));
    }

    // ── Store ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|integer|min:1|max:8',
            'section' => 'nullable|string|max:10',
            'effective_from' => 'required|date',
            'is_active' => 'nullable|boolean',
        ]);

        // Verify program belongs to department
        $program = Program::where('id', $data['program_id'])
            ->where('department_id', $deptId)
            ->firstOrFail();

        // Deactivate existing timetables for same program/semester if this is active
        if ($data['is_active'] ?? false) {
            Timetable::where('program_id', $data['program_id'])
                ->where('semester', $data['semester'])
                ->where('section', $data['section'] ?? null)
                ->update(['is_active' => false]);
        }

        $timetable = Timetable::create([
            'academic_session_id' => $data['academic_session_id'],
            'program_id' => $data['program_id'],
            'semester' => $data['semester'],
            'section' => $data['section'] ?? null,
            'effective_from' => $data['effective_from'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        return redirect()
            ->route('hod.timetable.index')
            ->with('success', 'Timetable created successfully.');
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Request $request, Timetable $timetable)
    {
        // Verify timetable belongs to department
        if ($timetable->program->department_id !== $this->currentDepartment($request)->id) {
            abort(403, 'Unauthorized access to timetable.');
        }

        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $academicSessions = AcademicSession::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get subjects and teachers for slot management
        $subjects = Subject::where('program_id', $timetable->program_id)
            ->where('semester', $timetable->semester)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        $teachers = Teacher::where('department_id', $deptId)
            ->with('user:id,name')
            ->where('is_active', true)
            ->get();

        $timetable->load(['slots' => fn ($q) => $q->with(['subject:id,name', 'teacher.user:id,name'])]);

        return view('hod.timetable.edit', compact(
            'timetable', 'department', 'programs', 'academicSessions', 'subjects', 'teachers'
        ));
    }

    // ── Update ─────────────────────────────────────────────────────────────
    public function update(Request $request, Timetable $timetable)
    {
        // Verify timetable belongs to department
        if ($timetable->program->department_id !== $this->currentDepartment($request)->id) {
            abort(403, 'Unauthorized access to timetable.');
        }

        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|integer|min:1|max:8',
            'section' => 'nullable|string|max:10',
            'effective_from' => 'required|date',
            'is_active' => 'nullable|boolean',
            // Slots data
            'slots' => 'nullable|array',
            'slots.*.day_of_week' => 'required|integer|min:1|max:7',
            'slots.*.start_time' => 'required|date_format:H:i',
            'slots.*.end_time' => 'required|date_format:H:i|after:slots.*.start_time',
            'slots.*.subject_id' => 'required|exists:subjects,id',
            'slots.*.teacher_id' => 'required|exists:teachers,id',
            'slots.*.room' => 'nullable|string|max:50',
        ]);

        // Verify program belongs to department
        $program = Program::where('id', $data['program_id'])
            ->where('department_id', $deptId)
            ->firstOrFail();

        DB::transaction(function () use ($data, $timetable, $deptId) {
            // Deactivate existing timetables if this is being activated
            if ($data['is_active'] ?? false) {
                Timetable::where('program_id', $data['program_id'])
                    ->where('semester', $data['semester'])
                    ->where('section', $data['section'] ?? null)
                    ->where('id', '!=', $timetable->id)
                    ->update(['is_active' => false]);
            }

            $timetable->update([
                'academic_session_id' => $data['academic_session_id'],
                'program_id' => $data['program_id'],
                'semester' => $data['semester'],
                'section' => $data['section'] ?? null,
                'effective_from' => $data['effective_from'],
                'is_active' => $data['is_active'] ?? false,
            ]);

            // Update slots if provided
            if (isset($data['slots'])) {
                // Delete existing slots
                $timetable->slots()->delete();

                // Create new slots
                foreach ($data['slots'] as $slotData) {
                    // Verify teacher belongs to department
                    $teacher = Teacher::where('id', $slotData['teacher_id'])
                        ->where('department_id', $deptId)
                        ->firstOrFail();

                    TimetableSlot::create([
                        'timetable_id' => $timetable->id,
                        'day_of_week' => $slotData['day_of_week'],
                        'start_time' => $slotData['start_time'],
                        'end_time' => $slotData['end_time'],
                        'subject_id' => $slotData['subject_id'],
                        'teacher_id' => $slotData['teacher_id'],
                        'room' => $slotData['room'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('hod.timetable.index')
            ->with('success', 'Timetable updated successfully.');
    }
}