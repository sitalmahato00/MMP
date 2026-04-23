<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'timetable_id', 'subject_id', 'teacher_id',
        'day_of_week', 'start_time', 'end_time',
        'room_number', 'type', 'group', 'duration',
    ];

    protected $casts = [
        // Removed datetime casting to prevent serialization issues
    ];

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /** Formatted time range display */
    public function getTimeRangeAttribute(): string
    {
        return date('H:i', strtotime($this->start_time)) . ' - ' . date('H:i', strtotime($this->end_time));
    }
}
