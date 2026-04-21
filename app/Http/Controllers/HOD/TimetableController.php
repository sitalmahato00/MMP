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
            'effective_from' => 'nullable|string', // BS date
            'effective_from_ad' => 'required|date', // AD date from hidden field
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
            'effective_from' => $data['effective_from_ad'], // Use AD date
            'is_active' => $data['is_active'] ?? false,
        ]);

        return redirect()
            ->route('hod.timetable.edit', $timetable)
            ->with('success', 'Timetable created successfully. Now add time slots.');
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function show(Request $request, Timetable $timetable)
    {
        // Verify timetable belongs to department
        if ($timetable->program->department_id !== $this->currentDepartment($request)->id) {
            abort(403, 'Unauthorized access to timetable.');
        }

        $department = $this->currentDepartment($request);

        $timetable->load([
            'program:id,name,code',
            'academicSession:id,name',
            'slots' => fn ($q) => $q->with(['subject:id,name,code', 'teacher.user:id,name'])->orderBy('day_of_week')->orderBy('start_time')
        ]);

        // Get subjects and teachers for reference
        $subjects = Subject::where('program_id', $timetable->program_id)
            ->where('semester', $timetable->semester)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        $teachers = Teacher::where('department_id', $department->id)
            ->with('user:id,name')
            ->where('is_active', true)
            ->get();

        return view('hod.timetable.show', compact(
            'timetable', 'department', 'subjects', 'teachers'
        ));
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

        // Transform slots for JavaScript
        $slotsData = $timetable->slots->map(function($slot) {
            return [
                'id' => $slot->id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : ($slot->start_time ?? '09:00'),
                'end_time' => $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : ($slot->end_time ?? '10:00'),
                'subject_id' => $slot->subject_id,
                'teacher_id' => $slot->teacher_id,
                'room_number' => $slot->room_number,
                'type' => $slot->type ?? 'theory',
                'group' => $slot->group,
                'duration' => $slot->duration ?? 1,
            ];
        })->values();

        return view('hod.timetable.edit', compact(
            'timetable', 'department', 'programs', 'academicSessions', 'subjects', 'teachers', 'slotsData'
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
            'effective_from' => 'nullable|string', // BS date
            'effective_from_ad' => 'required|date', // AD date from hidden field
            'is_active' => 'nullable|boolean',
            // Slots data
            'slots' => 'nullable|array',
            'slots.*.day_of_week' => 'required|string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'slots.*.start_time' => 'required|date_format:H:i',
            'slots.*.end_time' => 'required|date_format:H:i|after:slots.*.start_time',
            'slots.*.subject_id' => 'nullable|exists:subjects,id',
            'slots.*.teacher_id' => 'nullable|exists:teachers,id',
            'slots.*.room_number' => 'nullable|string|max:50',
            'slots.*.type' => 'nullable|string|in:theory,practical,lab,library,break',
            'slots.*.group' => 'nullable|string|max:50',
            'slots.*.duration' => 'nullable|integer|min:1|max:4',
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
                'effective_from' => $data['effective_from_ad'], // Use AD date
                'is_active' => $data['is_active'] ?? false,
            ]);

            // Update slots if provided
            if (isset($data['slots'])) {
                // Delete existing slots
                $timetable->slots()->delete();

                // Create new slots
                foreach ($data['slots'] as $slotData) {
                    // Skip teacher verification for break type
                    if (isset($slotData['teacher_id']) && $slotData['teacher_id'] && ($slotData['type'] ?? 'theory') !== 'break') {
                        // Verify teacher belongs to department
                        $teacher = Teacher::where('id', $slotData['teacher_id'])
                            ->where('department_id', $deptId)
                            ->firstOrFail();
                    }

                    TimetableSlot::create([
                        'timetable_id' => $timetable->id,
                        'day_of_week' => $slotData['day_of_week'],
                        'start_time' => $slotData['start_time'],
                        'end_time' => $slotData['end_time'],
                        'subject_id' => $slotData['subject_id'] ?? null,
                        'teacher_id' => $slotData['teacher_id'] ?? null,
                        'room_number' => $slotData['room_number'] ?? null,
                        'type' => $slotData['type'] ?? 'theory',
                        'group' => $slotData['group'] ?? null,
                        'duration' => $slotData['duration'] ?? 1,
                    ]);
                }
            }
        });

        return redirect()
            ->route('hod.timetable.index')
            ->with('success', 'Timetable updated successfully.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────
    public function destroy(Request $request, Timetable $timetable)
    {
        // Verify timetable belongs to department
        if ($timetable->program->department_id !== $this->currentDepartment($request)->id) {
            abort(403, 'Unauthorized access to timetable.');
        }

        DB::transaction(function () use ($timetable) {
            // Delete all slots first
            $timetable->slots()->delete();
            
            // Delete the timetable
            $timetable->delete();
        });

        return redirect()
            ->route('hod.timetable.index')
            ->with('success', 'Timetable deleted successfully.');
    }

    // ── Destroy Slot ───────────────────────────────────────────────────────
    public function destroySlot(Request $request, Timetable $timetable, TimetableSlot $slot)
    {
        // Verify timetable belongs to department
        if ($timetable->program->department_id !== $this->currentDepartment($request)->id) {
            abort(403, 'Unauthorized access to timetable.');
        }

        // Verify slot belongs to this timetable
        if ($slot->timetable_id !== $timetable->id) {
            abort(403, 'Slot does not belong to this timetable.');
        }

        $slot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slot deleted successfully.'
        ]);
    }

    // ── Export Timetable ───────────────────────────────────────────────────
    public function export(Request $request, Timetable $timetable)
    {
        // Verify timetable belongs to department
        if ($timetable->program->department_id !== $this->currentDepartment($request)->id) {
            abort(403, 'Unauthorized access to timetable.');
        }

        $format = $request->get('format', 'csv');
        $department = $this->currentDepartment($request);

        $timetable->load([
            'program:id,name,code',
            'academicSession:id,name',
            'slots' => fn ($q) => $q->with(['subject:id,name,code', 'teacher.user:id,name'])->orderBy('day_of_week')->orderBy('start_time')
        ]);

        // For PDF export, use specialized timetable template
        if ($format === 'pdf') {
            $filename = 'timetable_' . strtolower(str_replace(' ', '_', $timetable->program->name)) . '_sem' . $timetable->semester . '_' . date('Y-m-d') . '.pdf';
            
            // Get subjects and teachers
            $subjects = \App\Models\Subject::where('program_id', $timetable->program_id)
                ->where('semester', $timetable->semester)
                ->select('id', 'name', 'code')
                ->get();

            $teachers = \App\Models\Teacher::where('department_id', $department->id)
                ->with('user:id,name')
                ->where('is_active', true)
                ->get();

            $html = view('components.timetable-pdf-template', [
                'timetable' => $timetable,
                'department' => $department,
                'subjects' => $subjects,
                'teachers' => $teachers,
                'collegeName' => config('app.name', 'Technical College'),
                'collegeAddress' => \App\Models\SiteSetting::where('key', 'contact_address')->first()?->value ?? 'Nepal',
            ])->render();

            $pdf = app('dompdf.wrapper');
            $pdf->loadHTML($html);
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download($filename);
        }
        
        // For CSV export
        $config = $this->createTimetableExportConfig($timetable, $department, 'csv');
        $exportService = new \App\Services\ExportService();
        return $exportService->export($config);
    }

    // ── Create Timetable Export Config ─────────────────────────────────────
    private function createTimetableExportConfig(Timetable $timetable, $department, string $format): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        // Get unique time slots from actual data (only slots with classes)
        $timeSlots = $timetable->slots->map(function($slot) {
            $startTime = $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : $slot->start_time;
            $endTime = $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : $slot->end_time;
            return $startTime . '-' . $endTime;
        })->unique()->sort()->values()->toArray();

        // Group slots by day and time
        $slotsByDayTime = $timetable->slots->groupBy(function($slot) {
            $startTime = $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : $slot->start_time;
            $endTime = $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : $slot->end_time;
            return $slot->day_of_week . '-' . $startTime . '-' . $endTime;
        });

        $exportData = [];

        foreach ($days as $day) {
            foreach ($timeSlots as $timeSlot) {
                $slotKey = $day . '-' . $timeSlot;
                $slotsForTime = $slotsByDayTime->get($slotKey, collect());

                // Only add rows that have actual slots
                if ($slotsForTime->isNotEmpty()) {
                    // Check for common slots and group-specific slots
                    $commonSlots = $slotsForTime->filter(fn($slot) => empty($slot->group) || $slot->group === '');
                    $groupASlots = $slotsForTime->filter(fn($slot) => $slot->group === 'A');
                    $groupBSlots = $slotsForTime->filter(fn($slot) => $slot->group === 'B');

                    $commonSlot = $commonSlots->first();
                    $groupASlot = $groupASlots->first();
                    $groupBSlot = $groupBSlots->first();

                    $exportData[] = [
                        'day' => ucfirst($day),
                        'time_period' => $this->formatTimeRange($timeSlot),
                        'group_a_subject' => $groupASlot ? ($groupASlot->type === 'break' ? 'BREAK' : ($groupASlot->subject->name ?? '')) : '',
                        'group_a_teacher' => $groupASlot && $groupASlot->type !== 'break' ? ($groupASlot->teacher->user->name ?? '') : '',
                        'group_a_room' => $groupASlot ? ($groupASlot->room_number ?? '') : '',
                        'group_a_type' => $groupASlot ? ($groupASlot->type ?? '') : '',
                        'group_b_subject' => $groupBSlot ? ($groupBSlot->type === 'break' ? 'BREAK' : ($groupBSlot->subject->name ?? '')) : '',
                        'group_b_teacher' => $groupBSlot && $groupBSlot->type !== 'break' ? ($groupBSlot->teacher->user->name ?? '') : '',
                        'group_b_room' => $groupBSlot ? ($groupBSlot->room_number ?? '') : '',
                        'group_b_type' => $groupBSlot ? ($groupBSlot->type ?? '') : '',
                        'common_subject' => $commonSlot ? ($commonSlot->type === 'break' ? 'BREAK' : ($commonSlot->subject->name ?? '')) : '',
                        'common_teacher' => $commonSlot && $commonSlot->type !== 'break' ? ($commonSlot->teacher->user->name ?? '') : '',
                        'common_room' => $commonSlot ? ($commonSlot->room_number ?? '') : '',
                        'common_type' => $commonSlot ? ($commonSlot->type ?? '') : '',
                    ];
                }
            }
        }

        return [
            'format' => $format,
            'title' => 'Timetable - ' . $timetable->program->name,
            'subtitle' => 'Semester ' . $timetable->semester . ($timetable->section ? ' • Section ' . $timetable->section : '') . ' • ' . bsDate($timetable->effective_from, 'F Y'),
            'department' => $department->name,
            'metadata' => [
                'Program' => $timetable->program->name,
                'Semester' => $timetable->semester,
                'Section' => $timetable->section ?? 'All',
                'Academic Session' => $timetable->academicSession->name ?? 'N/A',
                'Effective From' => bsDate($timetable->effective_from, 'F d, Y'),
                'Status' => $timetable->is_active ? 'Active' : 'Inactive',
                'Total Slots' => $timetable->slots->count(),
            ],
            'columns' => [
                'day' => 'Day',
                'time_period' => 'Time Period',
                'group_a_subject' => 'Group A - Subject',
                'group_a_teacher' => 'Group A - Teacher',
                'group_a_room' => 'Group A - Room',
                'group_a_type' => 'Group A - Type',
                'group_b_subject' => 'Group B - Subject',
                'group_b_teacher' => 'Group B - Teacher',
                'group_b_room' => 'Group B - Room',
                'group_b_type' => 'Group B - Type',
                'common_subject' => 'Common - Subject',
                'common_teacher' => 'Common - Teacher',
                'common_room' => 'Common - Room',
                'common_type' => 'Common - Type',
            ],
            'data' => $exportData,
        ];
    }

    // ── Format Time Range ──────────────────────────────────────────────────
    private function formatTimeRange(string $timeRange): string
    {
        [$start, $end] = explode('-', $timeRange);
        return $this->formatTime($start) . ' - ' . $this->formatTime($end);
    }

    // ── Format Time ────────────────────────────────────────────────────────
    private function formatTime(string $time): string
    {
        [$hours, $minutes] = explode(':', $time);
        $h = (int)$hours;
        $ampm = $h >= 12 ? 'PM' : 'AM';
        $displayHour = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
        return $displayHour . ':' . $minutes . ' ' . $ampm;
    }

    // ── Check Teacher Conflicts ────────────────────────────────────────────
    public function checkTeacherConflicts(Request $request, Timetable $timetable)
    {
        $data = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'slot_id' => 'nullable|integer', // For editing existing slot
        ]);

        $conflicts = [];

        // Check conflicts within current timetable
        $localConflicts = TimetableSlot::where('timetable_id', $timetable->id)
            ->where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->when($data['slot_id'], fn($q) => $q->where('id', '!=', $data['slot_id']))
            ->get()
            ->filter(function($slot) use ($data) {
                return $this->timeOverlaps(
                    $slot->start_time, $slot->end_time,
                    $data['start_time'], $data['end_time']
                );
            });

        foreach ($localConflicts as $conflict) {
            $conflicts[] = [
                'type' => 'local',
                'message' => "Conflict in same timetable at {$conflict->start_time}-{$conflict->end_time}",
                'subject' => $conflict->subject->name ?? 'Unknown Subject'
            ];
        }

        // Check conflicts across other department timetables
        $crossDepartmentConflicts = TimetableSlot::whereHas('timetable', function($q) use ($timetable) {
                $q->where('id', '!=', $timetable->id)
                  ->where('is_active', true);
            })
            ->where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->with(['timetable.program', 'subject'])
            ->get()
            ->filter(function($slot) use ($data) {
                return $this->timeOverlaps(
                    $slot->start_time, $slot->end_time,
                    $data['start_time'], $data['end_time']
                );
            });

        foreach ($crossDepartmentConflicts as $conflict) {
            $conflicts[] = [
                'type' => 'cross_department',
                'message' => "Conflict with {$conflict->timetable->program->name} at {$conflict->start_time}-{$conflict->end_time}",
                'subject' => $conflict->subject->name ?? 'Unknown Subject',
                'program' => $conflict->timetable->program->name
            ];
        }

        return response()->json([
            'conflicts' => $conflicts,
            'has_conflicts' => count($conflicts) > 0
        ]);
    }

    // ── Get Available Groups ───────────────────────────────────────────────
    public function getAvailableGroups(Request $request, Timetable $timetable)
    {
        // Get groups from existing slots in this timetable
        $existingGroups = TimetableSlot::where('timetable_id', $timetable->id)
            ->whereNotNull('group')
            ->where('group', '!=', '')
            ->distinct()
            ->pluck('group')
            ->filter()
            ->values();

        // Default groups
        $defaultGroups = ['A', 'B', 'C', 'D'];

        // Merge and deduplicate
        $allGroups = collect($defaultGroups)
            ->merge($existingGroups)
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'groups' => $allGroups
        ]);
    }

    // ── Get Subject Teachers ───────────────────────────────────────────────
    public function getSubjectTeachers(Request $request, Timetable $timetable)
    {
        $subjectId = $request->get('subject_id');
        
        if (!$subjectId) {
            return response()->json(['teachers' => []]);
        }

        $subject = Subject::with(['teachers.user'])->find($subjectId);
        
        if (!$subject) {
            return response()->json(['teachers' => []]);
        }

        // Get assigned teachers for this subject
        $assignedTeachers = $subject->teachers()
            ->where('department_id', $this->currentDepartment($request)->id)
            ->where('is_active', true)
            ->with('user:id,name')
            ->get()
            ->map(function($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'role' => $teacher->pivot->role ?? 'teacher'
                ];
            });

        // If no assigned teachers, get all department teachers
        if ($assignedTeachers->isEmpty()) {
            $assignedTeachers = Teacher::where('department_id', $this->currentDepartment($request)->id)
                ->where('is_active', true)
                ->with('user:id,name')
                ->get()
                ->map(function($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->user->name,
                        'role' => 'teacher'
                    ];
                });
        }

        return response()->json([
            'teachers' => $assignedTeachers,
            'subject_type' => $subject->type ?? 'theory'
        ]);
    }

    // ── Helper: Check Time Overlap ─────────────────────────────────────────
    private function timeOverlaps($start1, $end1, $start2, $end2)
    {
        $s1 = strtotime($start1);
        $e1 = strtotime($end1);
        $s2 = strtotime($start2);
        $e2 = strtotime($end2);
        
        return $s1 < $e2 && $s2 < $e1;
    }
}