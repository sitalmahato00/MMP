<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id', 'student_id', 'student_note',
        'attachment', 'status', 'marks_obtained', 'teacher_feedback',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
    ];

    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function student() { return $this->belongsTo(Student::class); }
}
