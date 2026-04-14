<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'subject_id', 'program_id',
        'semester', 'section', 'title', 'description',
        'attachment', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function program() { return $this->belongsTo(Program::class); }
    public function submissions() { return $this->hasMany(AssignmentSubmission::class); }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>=', now());
    }
}
