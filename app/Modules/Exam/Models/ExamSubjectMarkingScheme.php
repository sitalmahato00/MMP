<?php

namespace App\Modules\Exam\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubjectMarkingScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'subject_id',
        'full_marks_internal_theory', 'pass_marks_internal_theory',
        'full_marks_external_theory', 'pass_marks_external_theory',
        'full_marks_internal_practical', 'pass_marks_internal_practical',
        'full_marks_external_practical', 'pass_marks_external_practical',
    ];

    protected $casts = [
        'full_marks_internal_theory' => 'decimal:2',
        'pass_marks_internal_theory' => 'decimal:2',
        'full_marks_external_theory' => 'decimal:2',
        'pass_marks_external_theory' => 'decimal:2',
        'full_marks_internal_practical' => 'decimal:2',
        'pass_marks_internal_practical' => 'decimal:2',
        'full_marks_external_practical' => 'decimal:2',
        'pass_marks_external_practical' => 'decimal:2',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // ─── Helpers ───────────────────────────────────────────

    /** Total theory marks (Int + Ext) */
    public function getTotalTheoryMarksAttribute(): float
    {
        return ($this->full_marks_internal_theory ?? 0) + ($this->full_marks_external_theory ?? 0);
    }

    /** Total practical marks (Int + Ext) */
    public function getTotalPracticalMarksAttribute(): float
    {
        return ($this->full_marks_internal_practical ?? 0) + ($this->full_marks_external_practical ?? 0);
    }

    /** Grand total marks */
    public function getTotalFullMarksAttribute(): float
    {
        return $this->total_theory_marks + $this->total_practical_marks;
    }
}