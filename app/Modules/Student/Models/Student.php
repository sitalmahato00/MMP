<?php

namespace App\Modules\Student\Models;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Mark;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'department_id', 'program_id', 'academic_session_id',
        'student_no', 'registration_number',
        'current_semester', 'section', 'batch', 'admission_date',
        'guardian_name', 'guardian_phone', 'blood_group',
        'status', 'is_archived',
        // roll_number is set by HOD only, not admin-fillable
    ];

    protected $casts = [
        'current_semester' => 'integer',
        'admission_date' => 'date',
        'is_archived' => 'boolean',
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

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'parent_student', 'student_id', 'parent_id')
            ->withTimestamps();
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function alumnus()
    {
        return $this->hasOne(Alumni::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInSession($query, $sessionId)
    {
        return $query->where('academic_session_id', $sessionId);
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeFinalSemester($query)
    {
        return $query->whereColumn('current_semester', '>=',
            \DB::raw('(SELECT p.total_semesters FROM programs p WHERE p.id = students.program_id)')
        );
    }

    // ─── Helpers ───────────────────────────────────────────

    public function isFinalSemester(): bool
    {
        return $this->current_semester >= $this->program->total_semesters;
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }
}
