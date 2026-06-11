<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_bs', 'start_date', 'end_date',
        'is_active', 'status', 'is_locked',
        'activated_at', 'ended_at', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'activated_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function semesters()
    {
        return $this->hasMany(AcademicSessionSemester::class)->orderBy('semester_number');
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'active');
    }

    // ─── Helpers ───────────────────────────────────────────

    /**
     * Get the currently active session (cached for performance).
     */
    public static function current(): ?self
    {
        $sessionId = cache()->remember('active_academic_session_id', 3600, function () {
            $session = static::where('is_active', true)->first();
            return $session?->id;
        });

        if ($sessionId === null) {
            return null;
        }

        return static::find($sessionId);
    }

    /**
     * Clear cache when session changes.
     */
    public static function clearSessionCache(): void
    {
        cache()->forget('active_academic_session');
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function getIsCurrentAttribute(): bool
    {
        return (bool) $this->is_active;
    }

    public function setIsCurrentAttribute($value): void
    {
        $this->attributes['is_active'] = (bool) $value;
    }
}
