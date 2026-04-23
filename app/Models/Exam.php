<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_session_id', 'department_id', 'name', 'type', 'category', 'assessment_number',
        'assessment_full_marks', 'assessment_pass_marks',
        'start_date', 'end_date', 'status',
        'marks_open', 'is_published', 'published_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'assessment_number' => 'integer',
        'marks_open' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
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

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'exam_subject_marking_schemes')
            ->withPivot([
                'full_marks_internal_theory',
                'pass_marks_internal_theory',
                'full_marks_external_theory',
                'pass_marks_external_theory',
                'full_marks_internal_practical',
                'pass_marks_internal_practical',
                'full_marks_external_practical',
                'pass_marks_external_practical',
            ])
            ->withTimestamps();
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function markingSchemes()
    {
        return $this->hasMany(ExamSubjectMarkingScheme::class);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_published || $this->status === 'results_published') {
            return 'Published';
        }

        return match ($this->status) {
            'ongoing' => 'Ongoing',
            'completed' => $this->marks_open ? 'Marks Pending' : 'Verifying',
            default => 'Upcoming',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'monthly_assessment' => 'Monthly Assessment',
            default => 'CTEVT Final',
        };
    }

    public function getStatusToneAttribute(): string
    {
        if ($this->is_published || $this->status === 'results_published') {
            return 'green';
        }

        return match ($this->status) {
            'ongoing' => 'orange',
            'completed' => $this->marks_open ? 'yellow' : 'purple',
            default => 'blue',
        };
    }

    public function getIsPublishedStateAttribute(): bool
    {
        return (bool) ($this->is_published || $this->status === 'results_published');
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
