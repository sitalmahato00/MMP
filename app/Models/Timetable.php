<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_session_id', 'program_id', 'semester', 'section',
        'start_date', 'effective_from', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'effective_from' => 'date',
        'is_active' => 'boolean',
    ];

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function slots()
    {
        return $this->hasMany(TimetableSlot::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get slots grouped by day (cached for performance).
     */
    public function slotsByDay()
    {
        return cache()->remember(
            "timetable_{$this->id}_slots_by_day",
            1800,
            fn () => $this->slots()
                ->with(['subject', 'teacher.user'])
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week')
        );
    }
}
