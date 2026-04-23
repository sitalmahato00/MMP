<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
     * - Uploaded by: Student's class teachers (teachers teaching their subjects)
     */
    public function scopeVisibleToStudent($query, Student $student)
    {
        $departmentId = $student->department_id ?: $student->program?->department_id;
        $programId = $student->program_id;
        $semester = $student->current_semester;

        // Get IDs of teachers teaching this student's subjects via subject_teacher pivot table
        $teacherUserIds = DB::table('subjects')
            ->where('subjects.program_id', $programId)
            ->where('subjects.semester', $semester)
            ->join('subject_teacher', 'subjects.id', '=', 'subject_teacher.subject_id')
            ->join('teachers', 'subject_teacher.teacher_id', '=', 'teachers.id')
            ->pluck('teachers.user_id')
            ->unique()
            ->toArray();

        return $query
            ->where(function($q) use ($departmentId) {
                $q->whereNull('department_id')
                  ->orWhere('department_id', $departmentId);
            })
            ->where(function($q) use ($programId) {
                $q->whereNull('program_id')
                  ->orWhere('program_id', $programId);
            })
            ->where(function($q) use ($semester) {
                $q->whereNull('semester');
                if ($semester !== null) {
                    $q->orWhere('semester', $semester);
                }
            })
            // Also include resources uploaded by student's class teachers
            ->orWhere(function($q) use ($teacherUserIds) {
                if (!empty($teacherUserIds)) {
                    $q->whereIn('uploaded_by', $teacherUserIds);
                }
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
