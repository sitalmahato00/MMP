<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id', 'semester', 'name', 'code', 'type',
        'full_marks_internal_theory', 'full_marks_external_theory',
        'pass_marks_internal_theory', 'pass_marks_external_theory',
        'full_marks_internal_practical', 'full_marks_external_practical',
        'pass_marks_internal_practical', 'pass_marks_external_practical',
        'credit_hours', 'is_active',
    ];

    protected $casts = [
        'semester' => 'integer',
        'credit_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher')
            ->withPivot('academic_session_id', 'section')
            ->withTimestamps();
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // ─── Helpers ───────────────────────────────────────────

    /** Total theory marks (Int + Ext) */
    public function getTotalTheoryMarksAttribute(): int
    {
        return $this->full_marks_internal_theory + $this->full_marks_external_theory;
    }

    /** Total practical marks (Int + Ext) */
    public function getTotalPracticalMarksAttribute(): int
    {
        return $this->full_marks_internal_practical + $this->full_marks_external_practical;
    }

    /** Grand total marks */
    public function getTotalFullMarksAttribute(): int
    {
        return $this->total_theory_marks + $this->total_practical_marks;
    }

    /** Total pass marks required */
    public function getTotalPassMarksAttribute(): int
    {
        return $this->pass_marks_internal_theory + $this->pass_marks_external_theory
            + $this->pass_marks_internal_practical + $this->pass_marks_external_practical;
    }

    public function hasTheory(): bool
    {
        return in_array($this->type, ['theory', 'both']);
    }

    public function hasPractical(): bool
    {
        return in_array($this->type, ['practical', 'both']);
    }
}
