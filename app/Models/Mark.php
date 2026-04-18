<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'student_id', 'subject_id', 'program_id', 'teacher_id',
        'semester',
        'internal_theory_marks', 'external_theory_marks',
        'internal_practical_marks', 'external_practical_marks',
        'is_absent', 'is_withheld', 'status', 'remarks',
    ];

    protected $casts = [
        'semester' => 'integer',
        'internal_theory_marks' => 'decimal:2',
        'external_theory_marks' => 'decimal:2',
        'internal_practical_marks' => 'decimal:2',
        'external_practical_marks' => 'decimal:2',
        'is_absent' => 'boolean',
        'is_withheld' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
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
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
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
        return $this->total_theory + $this->total_practical;
    }

    /** Check if student passed this subject */
    public function getIsPassedAttribute(): bool
    {
        if ($this->is_absent || $this->is_withheld) return false;

        $subject = $this->subject;

        $theoryPass = ($this->internal_theory_marks ?? 0) >= $subject->pass_marks_internal_theory
            && ($this->external_theory_marks ?? 0) >= $subject->pass_marks_external_theory;

        $practicalPass = true;
        if ($subject->hasPractical()) {
            $practicalPass = ($this->internal_practical_marks ?? 0) >= $subject->pass_marks_internal_practical
                && ($this->external_practical_marks ?? 0) >= $subject->pass_marks_external_practical;
        }

        return $theoryPass && $practicalPass;
    }

    /** Get result remark (Pass/Fail/Absent) */
    public function getResultRemarkAttribute(): string
    {
        if ($this->is_absent) return 'Absent';
        if ($this->is_withheld) return 'Withheld';
        return $this->is_passed ? 'Pass' : 'Fail';
    }
}
