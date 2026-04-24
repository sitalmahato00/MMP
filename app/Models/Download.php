<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Download extends Model
{
    protected $fillable = [
        'title', 
        'file_path', 
        'file_name', 
        'file_type', 
        'file_size', 
        'description', 
        'category', 
        'department_id', 
        'subject_id',
        'program_id',
        'semester',
        'is_public',
        'visibility',
        'uploaded_by'
    ];
    
    protected $casts = [
        'is_public' => 'boolean'
    ];
    
    public function department() 
    { 
        return $this->belongsTo(Department::class); 
    }
    
    public function subject() 
    { 
        return $this->belongsTo(Subject::class); 
    }
    
    public function program() 
    { 
        return $this->belongsTo(Program::class); 
    }
    
    public function uploader() 
    { 
        return $this->belongsTo(User::class, 'uploaded_by'); 
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to filter downloads visible to a specific student
     * Validation logic:
     * - Department: NULL (all departments) OR matches student's department
     * - Program: NULL (all programs) OR matches student's program
     * - Semester: NULL (all semesters) OR matches student's semester
     * - Subject: NULL OR belongs to the student's current program + semester
     *
     * Important:
     * All filters must compose with AND. Do not add a broad OR path based on
     * uploader, or unrelated resources can leak into the student's feed.
     */
    public function scopeVisibleToStudent($query, Student $student)
    {
        $departmentId = $student->department_id ?: $student->program?->department_id;
        $programId = $student->program_id;
        $semester = $student->current_semester;

        return $query
            ->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id')
                    ->orWhere('department_id', $departmentId);
            })
            ->where(function ($q) use ($programId) {
                $q->whereNull('program_id')
                    ->orWhere('program_id', $programId);
            })
            ->where(function ($q) use ($semester) {
                $q->whereNull('semester');

                if ($semester !== null) {
                    $q->orWhere('semester', $semester);
                }
            })
            ->where(function ($q) use ($programId, $semester) {
                $q->whereNull('subject_id')
                    ->orWhereHas('subject', function ($subjectQuery) use ($programId, $semester) {
                        $subjectQuery->where('program_id', $programId);

                        if ($semester !== null) {
                            $subjectQuery->where('semester', $semester);
                        }
                    });
            });
    }

    public function storageDisk(): string
    {
        return $this->is_public ? 'public' : 'private';
    }

    public function getFileUrlAttribute(): string
    {
        if (! $this->file_path) {
            return '';
        }

        return $this->is_public
            ? Storage::disk('public')->url($this->file_path)
            : route('admin.downloads.file', $this);
    }
}
