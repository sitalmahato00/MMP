<?php

namespace App\Services;

use App\Models\{AcademicSession, AcademicSessionSemester, AuditLog, Program, Student};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * SessionService — Manages academic session lifecycle.
 * Central engine controlling the entire system state.
 */
class SessionService
{
    public function __construct(private AlumniService $alumniService) {}

    /**
     * End the current active session and activate the next one.
     */
    public function switchTo(AcademicSession $session): array
    {
        $currentSession = AcademicSession::where('is_active', true)->first();

        if ($currentSession && $currentSession->id === $session->id) {
            return ['promoted' => 0, 'converted' => 0, 'failed' => 0, 'errors' => []];
        }

        $result = ['promoted' => 0, 'converted' => 0, 'failed' => 0, 'errors' => []];

        if ($currentSession) {
            $result = $this->end($currentSession);

            if (($result['failed'] ?? 0) > 0) {
                return $result;
            }
        }

        $this->activate($session);

        return $result;
    }

    /**
     * Activate a session. Deactivates all others first.
     * Only ONE session can be active at a time.
     */
    public function activate(AcademicSession $session): void
    {
        DB::transaction(function () use ($session) {
            AcademicSession::where('id', '!=', $session->id)
                ->where('status', '!=', 'ended')
                ->update(['is_active' => false, 'status' => 'upcoming']);

            $session->update([
                'is_active' => true,
                'status' => 'active',
                'activated_at' => now(),
            ]);

            AcademicSession::clearSessionCache();
            AuditLog::log('session_activated', $session);
        });
    }

    /**
     * Preview the impact of ending a session without executing it.
     * Returns counts of students that will be promoted vs graduated,
     * plus the upcoming semesters that will be auto-created.
     */
    public function previewEndImpact(AcademicSession $session): array
    {
        $activeStudents = Student::active()
            ->inSession($session->id)
            ->with(['program'])
            ->get();

        $toPromote = 0;
        $toGraduate = 0;
        $bySemester = [];

        foreach ($activeStudents as $student) {
            $sem = $student->current_semester;
            $key = 'Semester ' . $sem;

            if ($student->isFinalSemester()) {
                $toGraduate++;
                $bySemester[$key] = ($bySemester[$key] ?? ['promote' => 0, 'graduate' => 0]);
                $bySemester[$key]['graduate']++;
            } else {
                $toPromote++;
                $bySemester[$key] = ($bySemester[$key] ?? ['promote' => 0, 'graduate' => 0]);
                $bySemester[$key]['promote']++;
            }
        }

        ksort($bySemester);

        $runningSemesters = AcademicSessionSemester::where('academic_session_id', $session->id)
            ->whereIn('status', ['running', 'delayed', 'upcoming'])
            ->get();

        // Calculate what next-session semesters would be created
        $runningSemNums = $runningSemesters->pluck('semester_number')->all();
        $maxSemesters = (int) \App\Models\Program::max('total_semesters') ?: 8;

        $nextSemesterNumbers = collect($runningSemNums)
            ->map(fn ($num) => $num + 1)
            ->filter(fn ($num) => $num <= $maxSemesters)
            ->all();
        $nextSemesterNumbers[] = 1;
        $nextSemesterNumbers = array_values(array_unique($nextSemesterNumbers));
        sort($nextSemesterNumbers);

        $nextSession = AcademicSession::where('status', 'upcoming')
            ->where('id', '!=', $session->id)
            ->orderBy('start_date')
            ->first();

        return [
            'total_students' => $activeStudents->count(),
            'to_promote' => $toPromote,
            'to_graduate' => $toGraduate,
            'by_semester' => $bySemester,
            'running_semesters' => $runningSemesters->count(),
            'current_semester_numbers' => $runningSemNums,
            'next_semester_numbers' => $nextSemesterNumbers,
            'next_session_name' => $nextSession?->name,
        ];
    }

    /**
     * End a session with full student lifecycle management:
     *
     * 1. Promote non-final-semester students to the next semester
     * 2. Convert final-semester students to alumni
     * 3. Mark all semesters as completed
     * 4. Lock the session
     * 5. Auto-create upcoming semesters in the next session
     *    e.g. session had 1,3,5 → next session gets upcoming 2,4,6 + 1 (new admissions)
     */
    public function end(AcademicSession $session): array
    {
        try {
            return DB::transaction(function () use ($session) {
                // Collect running semester numbers before we mark them completed
                $runningSemesters = AcademicSessionSemester::where('academic_session_id', $session->id)
                    ->whereIn('status', ['running', 'delayed', 'upcoming'])
                    ->pluck('semester_number')
                    ->all();

                // ── Step 1: Promote non-final students ─────────────────
                $promoted = $this->promoteStudents($session);

                // ── Step 2: Convert final-semester students to alumni ──
                $alumniResult = $this->alumniService->convertFinalYearStudents($session);

                if (($alumniResult['failed'] ?? 0) > 0) {
                    throw new \RuntimeException(
                        implode('; ', $alumniResult['errors'] ?? ['Unable to convert final-semester students.'])
                    );
                }

                // ── Step 3: Mark all running semesters as completed ────
                AcademicSessionSemester::where('academic_session_id', $session->id)
                    ->where('status', '!=', 'completed')
                    ->update([
                        'status' => 'completed',
                        'is_active' => false,
                    ]);

                // ── Step 4: Lock the session ──────────────────────────
                $session->update([
                    'is_active' => false,
                    'status' => 'ended',
                    'is_locked' => true,
                    'ended_at' => now(),
                ]);

                // ── Step 5: Create upcoming semesters in next session ──
                $upcomingSemesters = $this->createUpcomingSemesters($session, $runningSemesters);

                AcademicSession::clearSessionCache();
                AuditLog::log('session_ended', $session);

                return [
                    'promoted' => $promoted,
                    'converted' => $alumniResult['converted'] ?? 0,
                    'upcoming_created' => count($upcomingSemesters),
                    'upcoming_semesters' => $upcomingSemesters,
                    'failed' => 0,
                    'errors' => [],
                ];
            });
        } catch (\Throwable $exception) {
            \Log::error('SessionService::end failed', [
                'session_id' => $session->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'promoted' => 0,
                'converted' => 0,
                'upcoming_created' => 0,
                'failed' => 1,
                'errors' => [$exception->getMessage()],
            ];
        }
    }

    /**
     * Preview the impact of advancing semesters.
     */
    public function previewAdvance(AcademicSession $session): array
    {
        $runningSemesters = $session->semesters()
            ->where('status', 'running')
            ->orderBy('semester_number')
            ->get();

        $runningNumbers = $runningSemesters->pluck('semester_number')->sort()->values()->all();
        $maxSemesters = (int) Program::max('total_semesters') ?: 6;

        // Calculate next semester numbers
        $nextNumbers = [];
        $graduatingNumbers = [];
        foreach ($runningNumbers as $num) {
            if ($num >= $maxSemesters) {
                $graduatingNumbers[] = $num;
            } else {
                $nextNumbers[] = $num + 1;
            }
        }

        // Determine if we need a new session
        $existingCompleted = $session->semesters()
            ->where('status', 'completed')
            ->pluck('semester_number')
            ->all();

        $conflictsWithExisting = !empty(array_intersect($nextNumbers, $existingCompleted));
        $needsNewSession = $conflictsWithExisting;

        // Include sem 1 for new admissions when staying in same session
        if (!$needsNewSession && !in_array(1, $nextNumbers)) {
            $nextNumbers[] = 1;
        }
        sort($nextNumbers);

        // Student counts
        $activeStudents = Student::active()->inSession($session->id)->with('program')->get();
        $toPromote = $activeStudents->reject(fn ($s) => $s->isFinalSemester())->count();
        $toGraduate = $activeStudents->filter(fn ($s) => $s->isFinalSemester())->count();

        // Per-semester breakdown
        $bySemester = [];
        foreach ($activeStudents as $student) {
            $key = 'Semester ' . $student->current_semester;
            $bySemester[$key] = $bySemester[$key] ?? ['promote' => 0, 'graduate' => 0];
            if ($student->isFinalSemester()) {
                $bySemester[$key]['graduate']++;
            } else {
                $bySemester[$key]['promote']++;
            }
        }
        ksort($bySemester);

        $targetSession = $session;
        if ($needsNewSession) {
            $targetSession = AcademicSession::where('status', 'upcoming')
                ->where('id', '!=', $session->id)
                ->orderBy('start_date')
                ->first();
        }

        return [
            'running_numbers' => $runningNumbers,
            'next_numbers' => $nextNumbers,
            'graduating_numbers' => $graduatingNumbers,
            'needs_new_session' => $needsNewSession,
            'target_session' => $targetSession?->name,
            'target_session_exists' => $targetSession !== null,
            'total_students' => $activeStudents->count(),
            'to_promote' => $toPromote,
            'to_graduate' => $toGraduate,
            'by_semester' => $bySemester,
            'max_semesters' => $maxSemesters,
        ];
    }

    /**
     * Advance all running semesters to the next cycle.
     *
     * CTEVT cycle: Even (2,4,6) → Odd (1,3,5) within same session
     *              Odd (1,3,5) → Even (2,4,6) needs new session
     *
     * Steps:
     * 1. Promote non-final students to next semester
     * 2. Graduate final-semester students → alumni
     * 3. Mark current running semesters as completed
     * 4. Create next semesters (in same session or new session)
     * 5. If new session needed: end current, activate next
     */
    public function advanceSemesters(AcademicSession $session): array
    {
        try {
            return DB::transaction(function () use ($session) {
                $runningSemesters = $session->semesters()
                    ->where('status', 'running')
                    ->orderBy('semester_number')
                    ->get();

                if ($runningSemesters->isEmpty()) {
                    return ['failed' => 1, 'errors' => ['No running semesters to advance.']];
                }

                $runningNumbers = $runningSemesters->pluck('semester_number')->sort()->values()->all();
                $maxSemesters = (int) Program::max('total_semesters') ?: 6;

                // Calculate next semester numbers
                $nextNumbers = [];
                foreach ($runningNumbers as $num) {
                    if ($num < $maxSemesters) {
                        $nextNumbers[] = $num + 1;
                    }
                }

                // Determine if next semesters conflict with existing completed ones
                $existingCompleted = $session->semesters()
                    ->where('status', 'completed')
                    ->pluck('semester_number')
                    ->all();

                $needsNewSession = !empty(array_intersect($nextNumbers, $existingCompleted));

                // Include sem 1 for new admissions when staying in same session
                if (!$needsNewSession && !in_array(1, $nextNumbers)) {
                    $nextNumbers[] = 1;
                }
                sort($nextNumbers);

                // ── Step 1: Promote students ──────────────────────────
                $promoted = $this->promoteStudents($session);

                // ── Step 2: Graduate final-semester students ──────────
                $alumniResult = $this->alumniService->convertFinalYearStudents($session);
                $converted = $alumniResult['converted'] ?? 0;

                if (($alumniResult['failed'] ?? 0) > 0) {
                    throw new \RuntimeException(
                        implode('; ', $alumniResult['errors'] ?? ['Failed to convert final-semester students.'])
                    );
                }

                // ── Step 3: Mark running semesters as completed ───────
                $session->semesters()
                    ->where('status', 'running')
                    ->update(['status' => 'completed', 'is_active' => false]);

                // ── Step 4: Determine target session ─────────────────
                $targetSession = $session;

                if ($needsNewSession) {
                    $nextSession = AcademicSession::where('status', 'upcoming')
                        ->where('id', '!=', $session->id)
                        ->orderBy('start_date')
                        ->first();

                    if (!$nextSession) {
                        throw new \RuntimeException(
                            'No upcoming session available. Create a new session first.'
                        );
                    }

                    // End current session
                    $session->update([
                        'is_active' => false,
                        'status' => 'ended',
                        'is_locked' => true,
                        'ended_at' => now(),
                    ]);

                    // Activate next session
                    $this->activate($nextSession);
                    $targetSession = $nextSession;
                }

                // ── Step 5: Create next semesters ────────────────────
                $created = [];
                $semesterStartDate = $needsNewSession
                    ? ($targetSession->start_date ?? now())
                    : now();
                $semesterEndDate = $this->semesterEndDate($semesterStartDate);

                foreach ($nextNumbers as $num) {
                    $exists = $targetSession->semesters()
                        ->where('semester_number', $num)
                        ->exists();

                    if (!$exists) {
                        $targetSession->semesters()->create([
                            'semester_number' => $num,
                            'start_date' => $semesterStartDate,
                            'end_date' => $semesterEndDate,
                            'status' => 'running',
                            'is_active' => true,
                        ]);
                        $created[] = $num;
                    } else {
                        // Activate existing upcoming semesters
                        $targetSession->semesters()
                            ->where('semester_number', $num)
                            ->where('status', 'upcoming')
                            ->update(['status' => 'running', 'is_active' => true]);
                        $created[] = $num;
                    }
                }

                AcademicSession::clearSessionCache();
                AuditLog::log('semesters_advanced', $targetSession, null, [
                    'from' => $runningNumbers,
                    'to' => $created,
                    'promoted' => $promoted,
                    'graduated' => $converted,
                    'new_session' => $needsNewSession,
                ]);

                return [
                    'promoted' => $promoted,
                    'converted' => $converted,
                    'created' => $created,
                    'target_session' => $targetSession->name,
                    'needs_new_session' => $needsNewSession,
                    'failed' => 0,
                    'errors' => [],
                ];
            });
        } catch (\Throwable $e) {
            \Log::error('SessionService::advanceSemesters failed', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'promoted' => 0,
                'converted' => 0,
                'created' => [],
                'failed' => 1,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Promote all active non-final-semester students to the next semester.
     *
     * Marks records are safe because they reference the original semester
     * via marks.semester and marks.exam_id — those foreign keys don't change.
     * Only the student's current_semester pointer advances.
     */
    private function promoteStudents(AcademicSession $session): int
    {
        $students = Student::active()
            ->inSession($session->id)
            ->with(['program'])
            ->get()
            ->reject(fn (Student $s) => $s->isFinalSemester());

        $promoted = 0;

        foreach ($students as $student) {
            $oldSemester = $student->current_semester;
            $newSemester = $oldSemester + 1;

            $student->update(['current_semester' => $newSemester]);

            AuditLog::log(
                'student_promoted',
                $student,
                ['current_semester' => $oldSemester],
                ['current_semester' => $newSemester]
            );

            $promoted++;
        }

        return $promoted;
    }

    /**
     * Auto-create upcoming semesters in the next session.
     *
     * Logic: current session had semesters [1, 3, 5]
     *   → promoted students are now in 2, 4, 6
     *   → next session needs: semester 1 (new admissions) + 2, 4, 6 (promoted)
     *   → but only if those semester numbers are valid (≤ max total_semesters)
     *
     * If no next upcoming session exists, one is NOT auto-created.
     */
    private function createUpcomingSemesters(AcademicSession $endedSession, array $runningSemesterNumbers): array
    {
        $nextSession = AcademicSession::where('status', 'upcoming')
            ->where('id', '!=', $endedSession->id)
            ->orderBy('start_date')
            ->first();

        if (!$nextSession) {
            return [];
        }

        // Calculate next semester numbers: each current +1 for promoted students
        $maxSemesters = (int) \App\Models\Program::max('total_semesters') ?: 8;
        $semesterStartDate = $nextSession->start_date ?? now();
        $semesterEndDate = $this->semesterEndDate($semesterStartDate);

        $nextSemesterNumbers = collect($runningSemesterNumbers)
            ->map(fn ($num) => $num + 1)
            ->filter(fn ($num) => $num <= $maxSemesters)
            ->all();

        // Always include semester 1 for new admissions
        $nextSemesterNumbers[] = 1;
        $nextSemesterNumbers = array_unique($nextSemesterNumbers);
        sort($nextSemesterNumbers);

        // Get already-existing semesters in the next session to avoid duplicates
        $existingSemesters = AcademicSessionSemester::where('academic_session_id', $nextSession->id)
            ->pluck('semester_number')
            ->all();

        $created = [];

        foreach ($nextSemesterNumbers as $semNum) {
            if (in_array($semNum, $existingSemesters, true)) {
                continue;
            }

            AcademicSessionSemester::create([
                'academic_session_id' => $nextSession->id,
                'semester_number' => $semNum,
                'start_date' => $semesterStartDate,
                'end_date' => $semesterEndDate,
                'status' => 'upcoming',
                'is_active' => false,
            ]);

            $created[] = $semNum;
        }

        if (!empty($created)) {
            AuditLog::log('upcoming_semesters_created', $nextSession, null, [
                'semester_numbers' => $created,
                'from_session' => $endedSession->name,
            ]);
        }

        return $created;
    }

    private function semesterEndDate(Carbon $startDate): Carbon
    {
        return $startDate->copy()->addMonthsNoOverflow(6);
    }
}
