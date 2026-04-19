<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubjectMarkingScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'subject_id',
        'full_marks_internal_theory',
        'pass_marks_internal_theory',
        'full_marks_external_theory',
        'pass_marks_external_theory',
        'full_marks_internal_practical',
        'pass_marks_internal_practical',
        'full_marks_external_practical',
        'pass_marks_external_practical',
    ];

    protected $casts = [
        'exam_id' => 'integer',
        'subject_id' => 'integer',
        'full_marks_internal_theory' => 'decimal:2',
        'pass_marks_internal_theory' => 'decimal:2',
        'full_marks_external_theory' => 'decimal:2',
        'pass_marks_external_theory' => 'decimal:2',
        'full_marks_internal_practical' => 'decimal:2',
        'pass_marks_internal_practical' => 'decimal:2',
        'full_marks_external_practical' => 'decimal:2',
        'pass_marks_external_practical' => 'decimal:2',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
