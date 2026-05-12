<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_session_id', 'teacher_id', 'subject_id', 'program_id',
        'semester', 'section', 'date', 'period',
    ];

    protected $casts = [
        'date' => 'date',
        'semester' => 'integer',
    ];

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Alias for attendances() relationship - used for withCount('records')
     */
    public function records()
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
}
