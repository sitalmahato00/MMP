<?php

namespace App\Modules\Exam\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mark extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'student_id', 'subject_id', 'program_id', 'teacher_id',
        'semester',
        'internal_theory_marks', 'external_theory_marks',
        'internal_practical_marks', 'external_practical_marks',
        'assessment_attendance_percent', 'assessment_full_marks', 'assessment_pass_marks', 'assessment_obtained_marks',
        'ctevt_full_marks_internal_theory', 'ctevt_pass_marks_internal_theory',
        'ctevt_full_marks_external_theory', 'ctevt_pass_marks_external_theory',
        'ctevt_full_marks_internal_practical', 'ctevt_pass_marks_internal_practical',
        'ctevt_full_marks_external_practical', 'ctevt_pass_marks_external_practical',
        'exam_attendance_date', 'was_present_on_exam_date', 'attendance_remarks',
        'is_absent', 'is_withheld', 'is_delayed', 'delay_reason', 'status', 'remarks',
    ];

    protected $casts = [
        'semester' => 'integer',
        'internal_theory_marks' => 'decimal:2',
        'external_theory_marks' => 'decimal:2',
        'internal_practical_marks' => 'decimal:2',
        'external_practical_marks' => 'decimal:2',
        'assessment_attendance_percent' => 'decimal:2',
        'assessment_full_marks' => 'decimal:2',
        'assessment_pass_marks' => 'decimal:2',
        'assessment_obtained_marks' => 'decimal:2',
        'ctevt_full_marks_internal_theory' => 'decimal:2',
        'ctevt_pass_marks_internal_theory' => 'decimal:2',
        'ctevt_full_marks_external_theory' => 'decimal:2',
        'ctevt_pass_marks_external_theory' => 'decimal:2',
        'ctevt_full_marks_internal_practical' => 'decimal:2',
        'ctevt_pass_marks_internal_practical' => 'decimal:2',
        'ctevt_full_marks_external_practical' => 'decimal:2',
        'ctevt_pass_marks_external_practical' => 'decimal:2',
        'exam_attendance_date' => 'date',
        'was_present_on_exam_date' => 'boolean',
        'is_absent' => 'boolean',
        'is_withheld' => 'boolean',
        'is_delayed' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('marks.status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('marks.status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('marks.status', 'approved');
    }

    public function scopePublished($query)
    {
        return $query->where('marks.status', 'published');
    }

    public function scopeVisibleToPortal($query)
    {
        return $query->where('marks.status', 'published')
            ->whereHas('exam', function ($examQuery) {
                $examQuery->where(function ($publishedQuery) {
                    $publishedQuery
                        ->where('is_published', true)
                        ->orWhere('status', 'results_published');
                });
            });
    }

    // ─── CTEVT Marks Calculation ───────────────────────────

    /** Total theory marks obtained */
    public function getTotalTheoryAttribute(): float
    {
        return ($this->internal_theory_marks ?? 0) + ($this->external_theory_marks ?? 0);
    }

    /** Total practical marks obtained */
    public function getTotalPracticalAttribute(): float
    {
        return ($this->internal_practical_marks ?? 0) + ($this->external_practical_marks ?? 0);
    }

    /** Grand total marks obtained */
    public function getTotalMarksAttribute(): float
    {
        if (($this->exam?->category ?? null) === 'monthly_assessment' && $this->assessment_obtained_marks !== null) {
            return (float) $this->assessment_obtained_marks;
        }

        return $this->total_theory + $this->total_practical;
    }

    /** Check if student passed this subject */
    public function getIsPassedAttribute(): bool
    {
        if ($this->is_absent || $this->is_withheld) return false;

        if (($this->exam?->category ?? null) === 'monthly_assessment') {
            // Assessment exam validation using marks stored in this record
            $full = (float) ($this->assessment_full_marks ?? 0);
            $pass = (float) ($this->assessment_pass_marks ?? 0);
            $obtained = (float) ($this->assessment_obtained_marks ?? 0);

            if ($full <= 0 || $this->assessment_obtained_marks === null) {
                return false;
            }

            return $obtained >= $pass;
        }

        // For CTEVT exams, ALWAYS use the single source of truth: exam scheme or subject defaults
        $scheme = $this->getMarkingScheme();
        
        $passInternalTheory = (float) ($scheme['pass_marks_internal_theory'] ?? 0);
        $passExternalTheory = (float) ($scheme['pass_marks_external_theory'] ?? 0);
        $passInternalPractical = (float) ($scheme['pass_marks_internal_practical'] ?? 0);
        $passExternalPractical = (float) ($scheme['pass_marks_external_practical'] ?? 0);
        $fullInternalPractical = (float) ($scheme['full_marks_internal_practical'] ?? 0);
        $fullExternalPractical = (float) ($scheme['full_marks_external_practical'] ?? 0);
        
        // Theory validation - BOTH internal and external must pass
        $theoryPass = true;
        if ($passInternalTheory > 0 || $passExternalTheory > 0) {
            $theoryPass = ($this->internal_theory_marks ?? 0) >= $passInternalTheory
                && ($this->external_theory_marks ?? 0) >= $passExternalTheory;
        }

        // Practical validation - BOTH internal and external must pass (if practical exists)
        $practicalPass = true;
        $practicalThresholdApplies = $fullInternalPractical > 0
            || $fullExternalPractical > 0
            || $passInternalPractical > 0
            || $passExternalPractical > 0;

        if ($practicalThresholdApplies) {
            $practicalPass = ($this->internal_practical_marks ?? 0) >= $passInternalPractical
                && ($this->external_practical_marks ?? 0) >= $passExternalPractical;
        }

        // Student must pass BOTH theory AND practical components
        return $theoryPass && $practicalPass;
    }

    /**
     * Get marking scheme for this mark (SINGLE SOURCE OF TRUTH)
     * Priority: exam_subject_marking_schemes > subject defaults
     */
    private function getMarkingScheme(): array
    {
        // Try to get from exam-specific marking scheme first
        if ($this->exam_id && $this->subject_id) {
            $scheme = \DB::table('exam_subject_marking_schemes')
                ->where('exam_id', $this->exam_id)
                ->where('subject_id', $this->subject_id)
                ->first();
            
            if ($scheme) {
                return [
                    'full_marks_internal_theory' => $scheme->full_marks_internal_theory,
                    'pass_marks_internal_theory' => $scheme->pass_marks_internal_theory,
                    'full_marks_external_theory' => $scheme->full_marks_external_theory,
                    'pass_marks_external_theory' => $scheme->pass_marks_external_theory,
                    'full_marks_internal_practical' => $scheme->full_marks_internal_practical,
                    'pass_marks_internal_practical' => $scheme->pass_marks_internal_practical,
                    'full_marks_external_practical' => $scheme->full_marks_external_practical,
                    'pass_marks_external_practical' => $scheme->pass_marks_external_practical,
                ];
            }
        }

        // Fallback to subject defaults
        if ($this->subject) {
            return [
                'full_marks_internal_theory' => $this->subject->full_marks_internal_theory ?? 0,
                'pass_marks_internal_theory' => $this->subject->pass_marks_internal_theory ?? 0,
                'full_marks_external_theory' => $this->subject->full_marks_external_theory ?? 0,
                'pass_marks_external_theory' => $this->subject->pass_marks_external_theory ?? 0,
                'full_marks_internal_practical' => $this->subject->full_marks_internal_practical ?? 0,
                'pass_marks_internal_practical' => $this->subject->pass_marks_internal_practical ?? 0,
                'full_marks_external_practical' => $this->subject->full_marks_external_practical ?? 0,
                'pass_marks_external_practical' => $this->subject->pass_marks_external_practical ?? 0,
            ];
        }

        // Default fallback
        return [
            'full_marks_internal_theory' => 0,
            'pass_marks_internal_theory' => 0,
            'full_marks_external_theory' => 0,
            'pass_marks_external_theory' => 0,
            'full_marks_internal_practical' => 0,
            'pass_marks_internal_practical' => 0,
            'full_marks_external_practical' => 0,
            'pass_marks_external_practical' => 0,
        ];
    }

    /** Get result remark (Pass/Fail/Absent) */
    public function getResultRemarkAttribute(): string
    {
        if ($this->is_absent) return 'Absent';
        if ($this->is_withheld) return 'Withheld';
        if ($this->is_delayed) return 'Delayed';
        return $this->is_passed ? 'Pass' : 'Fail';
    }
}
