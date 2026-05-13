<?php

namespace App\Modules\Academic\Models;

use App\Modules\Department\Models\Department;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id', 'coordinator_id', 'name', 'code', 'slug',
        'ctevt_code', 'affiliation_type',
        'total_semesters', 'duration_years',
        'description', 'eligibility', 'syllabus', 'is_active',
    ];

    protected $casts = [
        'total_semesters' => 'integer',
        'duration_years' => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(Teacher::class, 'coordinator_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ───────────────────────────────────────────

    public function getSyllabusUrlAttribute(): ?string
    {
        return $this->syllabus ? Storage::disk('public')->url($this->syllabus) : null;
    }

    public function getFinalSemester(): int
    {
        return $this->total_semesters;
    }

    /**
     * Get subjects for a specific semester (cached).
     */
    public function subjectsForSemester(int $semester)
    {
        return cache()->remember(
            "program_{$this->id}_semester_{$semester}_subjects",
            3600,
            fn () => $this->subjects()->where('semester', $semester)->get()
        );
    }
}
