<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    /**
     * Display a listing of timetables across all departments.
     */
    public function index(Request $request)
    {
        $query = Timetable::with([
            'academicSession:id,name',
            'program.department',
            'slots' => fn ($q) => $q->with(['subject:id,name,code', 'teacher.user:id,name'])
        ]);

        if ($request->filled('department_id')) {
            $query->whereHas('program', fn ($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $query->where(function ($q) use ($term) {
                $q->whereHas('program', fn ($pq) => $pq->where('name', 'like', "%{$term}%"))
                  ->orWhere('section', 'like', "%{$term}%");
            });
        }

        $timetables = $query->latest('effective_from')->paginate(20)->withQueryString();

        // Statistics
        $totalTimetables = Timetable::count();
        $activeTimetables = Timetable::where('is_active', true)->count();
        $totalSlots = TimetableSlot::count();

        // Dropdowns
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $academicSessions = AcademicSession::orderByDesc('is_active')->orderBy('name')->get();

        return view('admin.timetable.index', compact(
            'timetables',
            'departments',
            'programs',
            'academicSessions',
            'totalTimetables',
            'activeTimetables',
            'totalSlots'
        ));
    }

    /**
     * Show the form for creating a new timetable.
     */
    public function create()
    {
        $departments = Department::with('programs')->orderBy('name')->get();
        $programs = Program::with('department')->orderBy('name')->get();
        $academicSessions = AcademicSession::orderByDesc('is_active')->orderBy('name')->get();

        return view('admin.timetable.create', compact('departments', 'programs', 'academicSessions'));
    }

    /**
     * Store a newly created timetable in storage.
     */
    public function store(Request $request)
    {
        if ($request->filled('effective_from')) {
            $converted = adDate($request->input('effective_from'))?->format('Y-m-d')
                ?? $request->input('effective_from_ad');
            if ($converted) {
                $request->merge(['effective_from' => $converted]);
            }
        }
        if ($request->filled('start_date')) {
            $converted = adDate($request->input('start_date'))?->format('Y-m-d')
                ?? $request->input('start_date_ad');
            if ($converted) {
                $request->merge(['start_date' => $converted]);
            }
        }

        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'program_id'          => 'required|exists:programs,id',
            'semester'            => 'required|integer|min:1|max:6',
            'section'             => 'nullable|string|max:10',
            'start_date'          => 'nullable|date',
            'effective_from'      => 'required|date',
            'is_active'           => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['section'] = filled($data['section'] ?? null) ? trim((string) $data['section']) : null;

        // Check for duplicate timetable
        $existing = Timetable::where('academic_session_id', $data['academic_session_id'])
            ->where('program_id', $data['program_id'])
            ->where('semester', $data['semester'])
            ->where('section', $data['section'])
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', 'A timetable already exists for this program, semester, and section in the selected academic session.');
        }

        $timetable = Timetable::create($data);

        return redirect()->route('admin.timetable.show', $timetable)
            ->with('success', 'Timetable created successfully. You can now add schedule slots.');
    }

    /**
     * Display the specified timetable grid.
     */
    public function show(Timetable $timetable)
    {
        $timetable->load([
            'academicSession',
            'program.department',
            'slots' => fn ($q) => $q->with(['subject:id,name,code', 'teacher.user:id,name'])->orderBy('start_time')
        ]);

        $days = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];

        // Available subjects for this program & semester
        $subjects = Subject::where('program_id', $timetable->program_id)
            ->where('semester', $timetable->semester)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Available teachers
        $teachers = Teacher::with('user', 'department')->where('is_active', true)->get();

        return view('admin.timetable.show', compact('timetable', 'days', 'subjects', 'teachers'));
    }

    /**
     * Show the form for editing the specified timetable.
     */
    public function edit(Timetable $timetable)
    {
        $timetable->load(['academicSession', 'program.department']);
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $academicSessions = AcademicSession::orderByDesc('is_active')->orderBy('name')->get();

        return view('admin.timetable.edit', compact('timetable', 'departments', 'programs', 'academicSessions'));
    }

    /**
     * Update the specified timetable in storage.
     */
    public function update(Request $request, Timetable $timetable)
    {
        if ($request->filled('effective_from')) {
            $converted = adDate($request->input('effective_from'))?->format('Y-m-d')
                ?? $request->input('effective_from_ad');
            if ($converted) {
                $request->merge(['effective_from' => $converted]);
            }
        }
        if ($request->filled('start_date')) {
            $converted = adDate($request->input('start_date'))?->format('Y-m-d')
                ?? $request->input('start_date_ad');
            if ($converted) {
                $request->merge(['start_date' => $converted]);
            }
        }

        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'program_id'          => 'required|exists:programs,id',
            'semester'            => 'required|integer|min:1|max:6',
            'section'             => 'nullable|string|max:10',
            'start_date'          => 'nullable|date',
            'effective_from'      => 'required|date',
            'is_active'           => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['section'] = filled($data['section'] ?? null) ? trim((string) $data['section']) : null;

        $timetable->update($data);

        return redirect()->route('admin.timetable.show', $timetable)
            ->with('success', 'Timetable updated successfully.');
    }

    /**
     * Store a slot in timetable.
     */
    public function storeSlot(Request $request, Timetable $timetable)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'subject_id'  => 'required|exists:subjects,id',
            'teacher_id'  => 'nullable|exists:teachers,id',
            'room_no'     => 'nullable|string|max:50',
            'type'        => 'nullable|in:theory,practical,tutorial,other',
        ]);

        $validated['timetable_id'] = $timetable->id;
        $validated['type'] = $validated['type'] ?? 'theory';

        TimetableSlot::create($validated);

        return back()->with('success', 'Time slot added successfully.');
    }

    /**
     * Remove a slot from timetable.
     */
    public function destroySlot(Timetable $timetable, TimetableSlot $slot)
    {
        if ($slot->timetable_id !== $timetable->id) {
            abort(403);
        }

        $slot->delete();

        return back()->with('success', 'Time slot removed successfully.');
    }

    /**
     * Export timetable.
     */
    public function export(Timetable $timetable)
    {
        $timetable->load([
            'academicSession',
            'program.department',
            'slots' => fn ($q) => $q->with(['subject', 'teacher.user'])->orderBy('start_time')
        ]);

        $days = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];

        return view('admin.timetable.export', compact('timetable', 'days'));
    }

    /**
     * Check teacher conflicts (AJAX).
     */
    public function checkTeacherConflicts(Request $request, Timetable $timetable)
    {
        $teacherId = $request->input('teacher_id');
        $dayOfWeek = $request->input('day_of_week');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        if (!$teacherId || !$dayOfWeek || !$startTime || !$endTime) {
            return response()->json(['conflict' => false]);
        }

        $conflicts = TimetableSlot::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->whereHas('timetable', fn ($q) => $q->where('is_active', true)->where('academic_session_id', $timetable->academic_session_id))
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)->where('end_time', '>=', $endTime);
                  });
            })
            ->with(['timetable.program', 'subject'])
            ->get();

        return response()->json([
            'conflict' => $conflicts->isNotEmpty(),
            'conflicts' => $conflicts
        ]);
    }

    /**
     * Delete the specified timetable.
     */
    public function destroy(Timetable $timetable)
    {
        $timetable->slots()->delete();
        $timetable->delete();

        return redirect()->route('admin.timetable.index')
            ->with('success', 'Timetable deleted successfully.');
    }
}
