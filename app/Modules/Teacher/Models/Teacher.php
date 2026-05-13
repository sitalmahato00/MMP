<?php

namespace App\Modules\Teacher\Models;


use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Subject;
use App\Modules\Academic\Models\TimetableSlot;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Mark;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'department_id', 'employee_id', 'designation',
        'qualification', 'specialization', 'join_date',
        'employment_type', 'is_active',
    ];

    protected $casts = [
        'join_date' => 'date',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher')
            ->withPivot('academic_session_id', 'section', 'role')
            ->withTimestamps();
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeExcludeHods($query)
    {
        return $query->where('designation', '!=', 'HOD')
            ->whereDoesntHave('user', function($q) {
                $q->whereHas('roles', function($r) {
                    $r->where('name', 'hod');
                });
            });
    }

    public function scopeOnlyTeachers($query)
    {
        return $query->excludeHods();
    }

    // ─── Helpers ───────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    /**
     * Get subjects for the current active session (cached).
     */
    public function currentSubjects()
    {
        $session = AcademicSession::current();
        if (!$session) return collect();

        return cache()->remember(
            "teacher_{$this->id}_session_{$session->id}_subjects",
            1800,
            fn () => $this->subjects()->wherePivot('academic_session_id', $session->id)->get()
        );
    }
}
