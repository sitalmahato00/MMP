<?php

namespace App\Modules\Exam\Services;


/**
 * MarksService — Handles CTEVT mark entry, validation, and result computation.
 * Marks Flow: Teacher(draft) → HOD(approved) → Principal(published)
 */
use Illuminate\Support\Facades\Cache;use App\Modules\Student\Models\Student; use App\Modules\Teacher\Models\Teacher; use App\Modules\Academic\Models\Subject; use App\Modules\Exam\Models\Mark; use App\Modules\User\Models\User; use App\Modules\Exam\Models\Exam;

class MarksService
{
    /**
     * Get the complete marksheet for a student in an exam (CTEVT format).
     * Results are cached per student+exam combination.
     */
    public function getMarksheet(Student $student, Exam $exam): array
    {
        $cacheKey = "marksheet:student:{$student->id}:exam:{$exam->id}";

        return Cache::remember($cacheKey, 1800, function () use ($student, $exam) {
            $marks = Mark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'published')
                ->with('subject')
                ->get();

            $subjectRows = $marks->map(fn ($mark) => [
                'subject_name' => $mark->subject->name,
                'subject_code' => $mark->subject->code,
                'full_marks_int_theory' => $mark->subject->full_marks_internal_theory,
                'full_marks_ext_theory' => $mark->subject->full_marks_external_theory,
                'pass_marks_int_theory' => $mark->subject->pass_marks_internal_theory,
                'pass_marks_ext_theory' => $mark->subject->pass_marks_external_theory,
                'full_marks_int_practical' => $mark->subject->full_marks_internal_practical,
                'full_marks_ext_practical' => $mark->subject->full_marks_external_practical,
                'pass_marks_int_practical' => $mark->subject->pass_marks_internal_practical,
                'pass_marks_ext_practical' => $mark->subject->pass_marks_external_practical,
                'obtained_int_theory' => $mark->internal_theory_marks,
                'obtained_ext_theory' => $mark->external_theory_marks,
                'obtained_int_practical' => $mark->internal_practical_marks,
                'obtained_ext_practical' => $mark->external_practical_marks,
                'total_obtained' => $mark->total_marks,
                'is_passed' => $mark->is_passed,
                'result_remark' => $mark->result_remark,
                'is_absent' => $mark->is_absent,
            ]);

            $grandTotal = $marks->sum(fn ($m) => $m->total_marks);
            $totalFull = $marks->sum(fn ($m) => $m->subject->total_full_marks);
            $allPassed = $marks->every(fn ($m) => $m->is_passed);
            $percentage = $totalFull > 0 ? round(($grandTotal / $totalFull) * 100, 2) : 0;

            return [
                'student' => $student->load('user'),
                'exam' => $exam,
                'subjects' => $subjectRows,
                'grand_total' => $grandTotal,
                'total_full_marks' => $totalFull,
                'percentage' => $percentage,
                'result' => $allPassed ? 'Passed' : 'Failed',
                'division' => $this->getDivision($percentage),
            ];
        });
    }

    /**
     * CTEVT division classification
     */
    private function getDivision(float $percentage): string
    {
        return match (true) {
            $percentage >= 80 => 'Distinction',
            $percentage >= 65 => 'First Division',
            $percentage >= 50 => 'Second Division',
            $percentage >= 40 => 'Pass',
            default => 'Fail',
        };
    }

    /**
     * Advance marks status in the approval flow.
     * Teacher(draft→submitted) → HOD(submitted→approved) → Principal(approved→published)
     */
    public function advanceStatus(Mark $mark, string $newStatus): bool
    {
        $allowedTransitions = [
            'draft' => 'submitted',
            'submitted' => 'approved',
            'approved' => 'published',
        ];

        if (($allowedTransitions[$mark->status] ?? null) !== $newStatus) {
            return false;
        }

        $mark->update(['status' => $newStatus]);

        // Clear marksheet cache for this student/exam combo
        Cache::forget("marksheet:student:{$mark->student_id}:exam:{$mark->exam_id}");

        \App\Models\AuditLog::log(
            "mark_{$newStatus}",
            $mark,
            ['status' => $mark->getOriginal('status')],
            ['status' => $newStatus]
        );

        return true;
    }
}
