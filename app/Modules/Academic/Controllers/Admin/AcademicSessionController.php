<?php

namespace App\Modules\Academic\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\AcademicSession;
use App\Models\AcademicSessionSemester;
use App\Models\Department;
use App\Models\Student;
use App\Services\SessionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function __construct(private SessionService $sessionService)
    {
    }

    public function index(Request $request)
    {
        $sessionScope = $this->resolveSessionScope($request->string('session_scope')->toString());

        $sessions = AcademicSession::query()
            ->when($sessionScope === 'running', fn ($query) => $query->whereIn('status', ['active', 'upcoming']))
            ->when($sessionScope === 'archived', fn ($query) => $query->where('status', 'ended'))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get(['id', 'name', 'name_bs', 'start_date', 'end_date', 'status', 'is_active', 'is_locked']);

        $selectedSession = $this->resolveSelectedSession(
            $request->integer('session_id'),
            $sessions,
            AcademicSession::current()
        );

        $semesterFilter = $this->resolveSemesterFilter($request->string('semester_filter')->toString());
        $statusFilter = $this->resolveSemesterStatusFilter($request->string('status_filter')->toString());
        $lifecycleFilter = $this->resolveSemesterLifecycleFilter($request->string('lifecycle_filter')->toString());

        $allSemesters = $selectedSession
            ? AcademicSessionSemester::query()
                ->where('academic_session_id', $selectedSession->id)
                ->orderBy('semester_number')
                ->get()
            : collect();

        $filteredSemesters = $allSemesters
            ->when($semesterFilter !== null, fn (Collection $items) => $items->where('semester_number', $semesterFilter))
            ->when($statusFilter !== 'all', fn (Collection $items) => $items->where('status', $statusFilter))
            ->when($lifecycleFilter !== 'all', fn (Collection $items) => $items->where('is_active', $lifecycleFilter === 'running'))
            ->values();

        $semesterNumbers = $allSemesters->pluck('semester_number')->unique()->values();

        $studentCountsBySemester = $selectedSession && $semesterNumbers->isNotEmpty()
            ? Student::query()
                ->selectRaw('current_semester, COUNT(*) as total')
                ->where('academic_session_id', $selectedSession->id)
                ->where('status', 'active')
                ->whereIn('current_semester', $semesterNumbers->all())
                ->groupBy('current_semester')
                ->pluck('total', 'current_semester')
            : collect();

        $runningSemesters = $allSemesters
            ->where('is_active', true)
            ->whereIn('status', ['running', 'delayed'])
            ->values();

        $overview = [
            'status' => $selectedSession?->status,
            'statusLabel' => match ($selectedSession?->status) {
                'active' => 'Active',
                'upcoming' => 'Upcoming',
                'ended' => 'Archived',
                default => 'Not Set',
            },
            'departments' => Department::query()->where('is_active', true)->count(),
            'runningSemesters' => $runningSemesters->count(),
            'students' => $selectedSession
                ? Student::query()->where('academic_session_id', $selectedSession->id)->where('status', 'active')->count()
                : 0,
            'semesterStatusCounts' => [
                'upcoming' => $allSemesters->where('status', 'upcoming')->count(),
                'running' => $allSemesters->where('status', 'running')->count(),
                'delayed' => $allSemesters->where('status', 'delayed')->count(),
                'completed' => $allSemesters->where('status', 'completed')->count(),
                'total' => $allSemesters->count(),
            ],
        ];

        $timeline = $this->buildTimelineRows($filteredSemesters, $selectedSession);

        return view('admin.academic-sessions.index', [
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'sessionScope' => $sessionScope,
            'semesterFilter' => $semesterFilter,
            'statusFilter' => $statusFilter,
            'lifecycleFilter' => $lifecycleFilter,
            'overview' => $overview,
            'semesterCards' => $this->buildSemesterCards($filteredSemesters, $studentCountsBySemester),
            'allSemesters' => $allSemesters,
            'timelineRows' => $timeline['rows'],
            'timelineStart' => $timeline['start'],
            'timelineEnd' => $timeline['end'],
            'timelineMonths' => $timeline['months'],
            'timelineTodayPct' => $timeline['todayPct'],
            'departmentImpactRows' => $this->buildDepartmentImpactRows($selectedSession, $runningSemesters->pluck('semester_number')),
            'delayReasonOptions' => [
                'exam_late' => 'Exam late',
                'holidays' => 'Holidays',
                'internal_delay' => 'Internal delay',
                'admin_decision' => 'Admin decision',
            ],
            'semesterStatusOptions' => [
                'upcoming' => 'Upcoming',
                'running' => 'Running',
                'delayed' => 'Delayed',
                'completed' => 'Completed',
            ],
            'semesterNumberOptions' => range(1, 6),
        ]);
    }

    public function create()
    {
        return view('admin.academic-sessions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100|unique:academic_sessions',
            'name_bs'    => 'nullable|string|max:100',
            'start_date' => 'required|string|max:10',
            'end_date'   => 'required|string|max:10',
            'status'     => 'nullable|in:upcoming,active',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $data['start_date'] = NepaliDateHelper::toAD($data['start_date']);
        $data['end_date']   = NepaliDateHelper::toAD($data['end_date']);
        $activateImmediately = ($data['status'] ?? 'upcoming') === 'active';

        unset($data['status']);

        $academicSession = AcademicSession::create($data);

        if ($activateImmediately) {
            $result = $this->sessionService->switchTo($academicSession);

            if (($result['failed'] ?? 0) > 0) {
                return redirect()->route('admin.academic-sessions.index')
                    ->with('error', 'Session created, but the current session could not be ended automatically. The new session remains saved as upcoming.');
            }

            $message = "Session '{$data['name']}' created and activated.";

            if (($result['promoted'] ?? 0) > 0) {
                $message .= " {$result['promoted']} student(s) promoted to next semester.";
            }
            if (($result['converted'] ?? 0) > 0) {
                $message .= " {$result['converted']} final-semester student(s) moved to alumni.";
            }

            return redirect()->route('admin.academic-sessions.index')
                ->with('success', $message);
        }

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', "Session '{$data['name']}' created.");
    }

    public function edit(AcademicSession $academicSession)
    {
        return view('admin.academic-sessions.edit', ['session' => $academicSession]);
    }

    public function update(Request $request, AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Cannot edit an ended session.');

        $data = $request->validate([
            'name'       => "required|string|max:100|unique:academic_sessions,name,{$academicSession->id}",
            'name_bs'    => 'nullable|string|max:100',
            'start_date' => 'required|string|max:10',
            'end_date'   => 'required|string|max:10',
            'status'     => 'nullable|in:upcoming,active',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $data['start_date'] = NepaliDateHelper::toAD($data['start_date']);
        $data['end_date']   = NepaliDateHelper::toAD($data['end_date']);

        // Handle status change
        $newStatus = $data['status'] ?? $academicSession->status;
        unset($data['status']);

        $academicSession->update($data);

        // If changed to active, use the proper activation flow
        if ($newStatus === 'active' && !$academicSession->is_active) {
            $this->sessionService->activate($academicSession);
        } elseif ($newStatus === 'upcoming' && $academicSession->is_active) {
            $academicSession->update(['is_active' => false, 'status' => 'upcoming']);
            AcademicSession::clearSessionCache();
        }

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Session updated.');
    }

    public function destroy(AcademicSession $academicSession)
    {
        abort_if($academicSession->is_active || $academicSession->is_locked, 403, 'Cannot delete an active or ended session.');
        $academicSession->delete();
        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Session deleted.');
    }

    public function storeSemester(Request $request, AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Cannot modify semesters for an archived session.');

        $data = $request->validate([
            'semester_number' => 'required|integer|min:1|max:6|unique:academic_session_semesters,semester_number,NULL,id,academic_session_id,' . $academicSession->id,
            'start_date' => 'required|string|max:10',
            'end_date' => 'nullable|string|max:10',
            'status' => 'required|in:upcoming,running,delayed,completed',
            'delay_reason' => 'nullable|required_if:status,delayed|in:exam_late,holidays,internal_delay,admin_decision',
            'notes' => 'nullable|string|max:500',
        ]);

        $startDateInput = trim((string) ($data['start_date'] ?? ''));
        $endDateInput = trim((string) ($data['end_date'] ?? ''));

        $data['start_date'] = NepaliDateHelper::toAD($startDateInput);
        $data['end_date'] = NepaliDateHelper::toAD($endDateInput);
        $data['is_active'] = $request->boolean('is_active');

        if ($data['status'] !== 'delayed') {
            $data['delay_reason'] = null;
        }

        if (!$data['start_date']) {
            return back()->withErrors(['start_date' => 'Invalid BS date format.'])->withInput();
        }

        if (!$data['end_date']) {
            if ($endDateInput === '') {
                $data['end_date'] = $this->defaultSemesterEndDate($data['start_date']);
            } else {
                return back()->withErrors(['end_date' => 'Invalid BS date format.'])->withInput();
            }
        }

        if (Carbon::parse($data['end_date'])->lt(Carbon::parse($data['start_date']))) {
            return back()->withErrors(['end_date' => 'End date must be after start date.'])->withInput();
        }

        $academicSession->semesters()->create($data);

        return back()->with('success', 'Semester setup added successfully.');
    }

    public function updateSemester(Request $request, AcademicSession $academicSession, AcademicSessionSemester $semester)
    {
        abort_if($academicSession->is_locked, 403, 'Cannot modify semesters for an archived session.');
        abort_unless((int) $semester->academic_session_id === (int) $academicSession->id, 404);

        $data = $request->validate([
            'semester_number' => 'required|integer|min:1|max:6|unique:academic_session_semesters,semester_number,' . $semester->id . ',id,academic_session_id,' . $academicSession->id,
            'start_date' => 'required|string|max:10',
            'end_date' => 'nullable|string|max:10',
            'status' => 'required|in:upcoming,running,delayed,completed',
            'delay_reason' => 'nullable|required_if:status,delayed|in:exam_late,holidays,internal_delay,admin_decision',
            'notes' => 'nullable|string|max:500',
        ]);

        $startDateInput = trim((string) ($data['start_date'] ?? ''));
        $endDateInput = trim((string) ($data['end_date'] ?? ''));

        $data['start_date'] = NepaliDateHelper::toAD($startDateInput);
        $data['end_date'] = NepaliDateHelper::toAD($endDateInput);
        $data['is_active'] = $request->boolean('is_active');

        if ($data['status'] !== 'delayed') {
            $data['delay_reason'] = null;
        }

        if (!$data['start_date']) {
            return back()->withErrors(['start_date' => 'Invalid BS date format.'])->withInput();
        }

        if (!$data['end_date']) {
            if ($endDateInput === '') {
                $data['end_date'] = $this->defaultSemesterEndDate($data['start_date']);
            } else {
                return back()->withErrors(['end_date' => 'Invalid BS date format.'])->withInput();
            }
        }

        if (Carbon::parse($data['end_date'])->lt(Carbon::parse($data['start_date']))) {
            return back()->withErrors(['end_date' => 'End date must be after start date.'])->withInput();
        }

        $semester->update($data);

        return back()->with('success', 'Semester setup updated successfully.');
    }

    public function destroySemester(AcademicSession $academicSession, AcademicSessionSemester $semester)
    {
        abort_if($academicSession->is_locked, 403, 'Cannot modify semesters for an archived session.');
        abort_unless((int) $semester->academic_session_id === (int) $academicSession->id, 404);

        $semester->delete();

        return back()->with('success', 'Semester ' . $semester->semester_number . ' deleted.');
    }

    public function setCurrent(AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Cannot activate an ended session.');

        $result = $this->sessionService->switchTo($academicSession);

        if (($result['failed'] ?? 0) > 0) {
            return back()->with('error', 'Unable to switch sessions automatically. The current session was not closed cleanly.');
        }

        $message = "'{$academicSession->name}' is now the active session.";

        if (($result['promoted'] ?? 0) > 0) {
            $message .= " {$result['promoted']} student(s) promoted to next semester.";
        }
        if (($result['converted'] ?? 0) > 0) {
            $message .= " {$result['converted']} final-semester student(s) moved to alumni.";
        }

        return back()->with('success', $message);
    }

    /**
     * Preview what will happen when the session ends (AJAX endpoint).
     */
    public function previewEnd(AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Session is already ended.');
        abort_unless($academicSession->is_active, 403, 'Only the active session can be ended.');

        $preview = $this->sessionService->previewEndImpact($academicSession);

        return response()->json($preview);
    }

    /**
     * End the active session — promotes students and graduates final semester.
     */
    public function endSession(Request $request, AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Session is already ended.');
        abort_unless($academicSession->is_active, 403, 'Only the active session can be ended.');

        // Require explicit confirmation
        $request->validate([
            'confirm_end' => 'required|accepted',
        ]);

        $result = $this->sessionService->end($academicSession);

        if (($result['failed'] ?? 0) > 0) {
            return back()->with('error', 'Session could not be ended: ' . implode('; ', $result['errors'] ?? []));
        }

        $message = "Session '{$academicSession->name}' has been ended and locked.";

        if (($result['promoted'] ?? 0) > 0) {
            $message .= " {$result['promoted']} student(s) promoted to next semester.";
        }
        if (($result['converted'] ?? 0) > 0) {
            $message .= " {$result['converted']} final-semester student(s) graduated to alumni.";
        }
        if (($result['upcoming_created'] ?? 0) > 0) {
            $sems = implode(', ', $result['upcoming_semesters'] ?? []);
            $message .= " Upcoming semesters ({$sems}) created in next session.";
        }

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', $message);
    }

    /**
     * Preview what will happen when semesters are advanced (AJAX endpoint).
     */
    public function previewAdvance(AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Session is already ended.');
        abort_unless($academicSession->is_active, 403, 'Only the active session can advance.');

        $preview = $this->sessionService->previewAdvance($academicSession);

        return response()->json($preview);
    }

    /**
     * Advance all running semesters to the next cycle.
     */
    public function advanceSemesters(Request $request, AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Session is already ended.');
        abort_unless($academicSession->is_active, 403, 'Only the active session can advance.');

        $data = $request->validate([
            'confirm_advance' => 'required|accepted',
            'selected_semesters' => 'required|array|min:1',
            'selected_semesters.*' => 'integer|min:1',
        ]);

        $result = $this->sessionService->advanceSemesters($academicSession, $data['selected_semesters'] ?? []);

        if (($result['failed'] ?? 0) > 0) {
            $error = 'Advance failed: ' . implode('; ', $result['errors'] ?? []);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $error], 422);
            }
            return back()
                ->with('error', $error)
                ->with('open_advance_modal', true)
                ->withInput();
        }

        $created = implode(', ', $result['created'] ?? []);
        $message = ($result['created_new_session'] ?? false)
            ? "New session '{$result['target_session']}' was created automatically and activated."
            : "Semesters advanced successfully.";

        if (($result['promoted'] ?? 0) > 0) {
            $message .= " {$result['promoted']} student(s) promoted.";
        }
        if (($result['converted'] ?? 0) > 0) {
            $message .= " {$result['converted']} student(s) graduated to alumni.";
        }
        if (!empty($created)) {
            $message .= " Now running: Semester {$created}";
            if ($result['needs_new_session'] ?? false) {
                $message .= " in '{$result['target_session']}'.";
            } else {
                $message .= ".";
            }
        }

        $redirectUrl = route('admin.academic-sessions.index');
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $message, 'redirect' => $redirectUrl]);
        }

        return redirect($redirectUrl)->with('success', $message);
    }

    private function resolveSessionScope(string $scope): string
    {
        return in_array($scope, ['all', 'running', 'archived'], true) ? $scope : 'all';
    }

    private function resolveSemesterFilter(string $filter): ?int
    {
        if (!is_numeric($filter)) {
            return null;
        }

        $semesterNumber = (int) $filter;
        return $semesterNumber >= 1 && $semesterNumber <= 8 ? $semesterNumber : null;
    }

    private function resolveSemesterStatusFilter(string $filter): string
    {
        return in_array($filter, ['all', 'upcoming', 'running', 'delayed', 'completed'], true) ? $filter : 'all';
    }

    private function resolveSemesterLifecycleFilter(string $filter): string
    {
        return in_array($filter, ['all', 'running', 'archived'], true) ? $filter : 'all';
    }

    private function defaultSemesterEndDate(Carbon $startDate): Carbon
    {
        return $startDate->copy()->addMonthsNoOverflow(6);
    }

    private function resolveSelectedSession(?int $sessionId, Collection $sessions, ?AcademicSession $fallback): ?AcademicSession
    {
        if ($sessionId) {
            $found = $sessions->firstWhere('id', $sessionId);
            if ($found) {
                return $found;
            }

            return AcademicSession::query()->find($sessionId);
        }

        return $fallback ?? $sessions->first();
    }

    private function buildSemesterCards(Collection $semesters, Collection $studentCountsBySemester): array
    {
        return $semesters->map(function (AcademicSessionSemester $semester) use ($studentCountsBySemester) {
            $delayReason = $this->delayReasonLabel($semester->delay_reason);
            $statusLabel = match ($semester->status) {
                'upcoming' => 'Upcoming',
                'running' => 'Running',
                'delayed' => $delayReason ? 'Delayed - ' . $delayReason : 'Delayed',
                'completed' => 'Completed',
                default => ucfirst($semester->status),
            };

            $tone = match ($semester->status) {
                'upcoming' => 'sky',
                'running' => 'emerald',
                'delayed' => in_array($semester->delay_reason, ['exam_late', 'admin_decision'], true) ? 'rose' : 'amber',
                'completed' => 'slate',
                default => 'slate',
            };

            return [
                'id' => $semester->id,
                'semester_number' => $semester->semester_number,
                'title' => 'Semester ' . $semester->semester_number,
                'start_date_bs' => bsDate($semester->start_date, 'Y, F d'),
                'end_date_bs' => bsDate($semester->end_date, 'Y, F d'),
                'start_date_input' => bsDate($semester->start_date),
                'end_date_input' => bsDate($semester->end_date),
                'status' => $semester->status,
                'status_label' => $statusLabel,
                'status_tone' => $tone,
                'delay_reason' => $semester->delay_reason,
                'delay_reason_label' => $delayReason,
                'is_active' => (bool) $semester->is_active,
                'notes' => (string) ($semester->notes ?? ''),
                'students' => (int) ($studentCountsBySemester[$semester->semester_number] ?? 0),
                'progress' => $this->progressForSemester($semester),
            ];
        })->values()->all();
    }

    private function progressForSemester(AcademicSessionSemester $semester): int
    {
        if ($semester->status === 'completed') {
            return 100;
        }

        $start = Carbon::parse($semester->start_date)->startOfDay();
        $end = Carbon::parse($semester->end_date)->endOfDay();
        $today = now();

        if ($end->lt($start)) {
            return 0;
        }

        if ($today->lte($start)) {
            return 0;
        }

        if ($today->gte($end)) {
            return 100;
        }

        $totalDays = max($start->diffInDays($end), 1);
        $elapsedDays = $start->diffInDays($today);

        return (int) max(0, min(round(($elapsedDays / $totalDays) * 100), 100));
    }

    private function buildTimelineRows(Collection $semesters, ?AcademicSession $session): array
    {
        if ($semesters->isEmpty()) {
            return [
                'start' => $session?->start_date ? bsDate($session->start_date, 'Y, F d') : null,
                'end' => $session?->end_date ? bsDate($session->end_date, 'Y, F d') : null,
                'rows' => [],
                'months' => [],
                'todayPct' => null,
            ];
        }

        $globalStart = Carbon::parse($semesters->min('start_date'))->startOfDay();
        $globalEnd = Carbon::parse($semesters->max('end_date'))->endOfDay();

        if ($session?->start_date) {
            $sessionStart = Carbon::parse($session->start_date)->startOfDay();
            if ($sessionStart->lt($globalStart)) {
                $globalStart = $sessionStart;
            }
        }

        if ($session?->end_date) {
            $sessionEnd = Carbon::parse($session->end_date)->endOfDay();
            if ($sessionEnd->gt($globalEnd)) {
                $globalEnd = $sessionEnd;
            }
        }

        // Extend to full month boundaries for cleaner axis
        $globalStart = $globalStart->copy()->startOfMonth();
        $globalEnd = $globalEnd->copy()->endOfMonth();

        $totalDays = max($globalStart->diffInDays($globalEnd) + 1, 1);

        // ── Build month ticks for horizontal axis ──────────
        $months = [];
        $cursor = $globalStart->copy()->startOfMonth();
        while ($cursor->lte($globalEnd)) {
            $monthStart = $cursor->copy();
            $monthEnd = $cursor->copy()->endOfMonth();
            if ($monthEnd->gt($globalEnd)) {
                $monthEnd = $globalEnd->copy();
            }

            $leftDays = max($globalStart->diffInDays($monthStart, false), 0);
            $leftPct = round(($leftDays / $totalDays) * 100, 2);
            $durationDays = max($monthStart->diffInDays($monthEnd) + 1, 1);
            $widthPct = round(($durationDays / $totalDays) * 100, 2);

            $months[] = [
                'label' => bsDate($monthStart->copy()->addDays(14), 'F'),  // mid-month for reliable BS month
                'year' => bsDate($monthStart->copy()->addDays(14), 'Y'),
                'left' => $leftPct,
                'width' => $widthPct,
            ];

            $cursor->addMonth()->startOfMonth();
        }

        // ── Today marker ───────────────────────────────────
        $today = Carbon::today();
        $todayPct = null;
        if ($today->gte($globalStart) && $today->lte($globalEnd)) {
            $todayDays = $globalStart->diffInDays($today);
            $todayPct = round(($todayDays / $totalDays) * 100, 2);
        }

        // ── Build semester bars ────────────────────────────
        $rows = $semesters->sortBy('semester_number')->values()->map(function (AcademicSessionSemester $semester) use ($globalStart, $totalDays) {
            $start = Carbon::parse($semester->start_date)->startOfDay();
            $end = Carbon::parse($semester->end_date)->endOfDay();

            $leftDays = max($globalStart->diffInDays($start, false), 0);
            $leftPct = round(($leftDays / $totalDays) * 100, 2);

            $durationDays = max($start->diffInDays($end) + 1, 1);
            $widthPct = round(($durationDays / $totalDays) * 100, 2);
            if ($leftPct + $widthPct > 100) {
                $widthPct = max(100 - $leftPct, 2);
            }

            $barClass = match ($semester->status) {
                'upcoming' => 'bg-sky-400',
                'running' => 'bg-emerald-500',
                'delayed' => in_array($semester->delay_reason, ['exam_late', 'admin_decision'], true) ? 'bg-rose-500' : 'bg-amber-400',
                'completed' => 'bg-slate-400',
                default => 'bg-slate-400',
            };

            $dotClass = match ($semester->status) {
                'upcoming' => 'bg-sky-300',
                'running' => 'bg-emerald-300',
                'delayed' => 'bg-rose-300',
                'completed' => 'bg-slate-300',
                default => 'bg-slate-300',
            };

            return [
                'semester_number' => $semester->semester_number,
                'status' => $semester->status,
                'status_label' => match ($semester->status) {
                    'upcoming' => 'Upcoming',
                    'running' => 'Running',
                    'delayed' => 'Delayed',
                    'completed' => 'Completed',
                    default => ucfirst($semester->status),
                },
                'start_label' => bsDate($start, 'Y, F d'),
                'end_label' => bsDate($end, 'Y, F d'),
                'left' => $leftPct,
                'width' => max($widthPct, 2),
                'barClass' => $barClass,
                'dotClass' => $dotClass,
            ];
        })->all();

        return [
            'start' => bsDate($globalStart, 'Y, F d'),
            'end' => bsDate($globalEnd, 'Y, F d'),
            'rows' => $rows,
            'months' => $months,
            'todayPct' => $todayPct,
        ];
    }

    private function buildDepartmentImpactRows(?AcademicSession $session, Collection $runningSemesterNumbers): array
    {
        if (!$session) {
            return [];
        }

        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        if ($departments->isEmpty()) {
            return [];
        }

        $semesterNumbers = $runningSemesterNumbers
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->sort()
            ->values();

        $grouped = collect();

        if ($semesterNumbers->isNotEmpty()) {
            $grouped = Student::query()
                ->selectRaw('department_id, current_semester, COUNT(*) as total_students, MIN(admission_date) as min_admission, MAX(admission_date) as max_admission')
                ->where('academic_session_id', $session->id)
                ->where('status', 'active')
                ->whereIn('current_semester', $semesterNumbers->all())
                ->groupBy('department_id', 'current_semester')
                ->get()
                ->groupBy('department_id');
        }

        $semesterLabel = $semesterNumbers->isNotEmpty()
            ? 'Sem ' . $semesterNumbers->implode(', ')
            : 'Not started';

        return $departments->map(function (Department $department) use ($grouped, $semesterLabel) {
            $departmentRows = $grouped->get($department->id, collect());
            $studentCount = (int) $departmentRows->sum('total_students');

            $dateCandidates = collect();
            foreach ($departmentRows as $row) {
                if (!empty($row->min_admission)) {
                    $dateCandidates->push($row->min_admission);
                }
                if (!empty($row->max_admission)) {
                    $dateCandidates->push($row->max_admission);
                }
            }

            $dateWindow = 'Follows session timeline';
            if ($dateCandidates->isNotEmpty()) {
                $dateWindow = bsDate($dateCandidates->min(), 'd F Y') . ' - ' . bsDate($dateCandidates->max(), 'd F Y');
            }

            return [
                'department' => $department->name,
                'code' => $department->code,
                'semester_label' => $semesterLabel,
                'date_window' => $dateWindow,
                'students' => $studentCount,
            ];
        })->values()->all();
    }

    private function delayReasonLabel(?string $delayReason): ?string
    {
        return match ($delayReason) {
            'exam_late' => 'Exam late',
            'holidays' => 'Holidays',
            'internal_delay' => 'Internal delay',
            'admin_decision' => 'Admin decision',
            default => null,
        };
    }
}
