<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_session_id', 'department_id', 'name', 'type',
        'start_date', 'end_date', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'exam_program')
            ->withPivot('semester')
            ->withTimestamps();
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('academic_session_id', $sessionId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
