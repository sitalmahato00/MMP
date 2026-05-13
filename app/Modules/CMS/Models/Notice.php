<?php

namespace App\Modules\CMS\Models;


use App\Modules\Academic\Models\Program;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Exam;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'content', 'attachment', 'type',
        'department_id', 'program_id', 'semester',
        'created_by', 'is_published', 'published_at',
    ];

    protected $casts = [
        'semester' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function attachments()
    {
        return $this->hasMany(NoticeAttachment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeGeneral($query)
    {
        return $query->where('type', 'general');
    }

    public function scopeForNoticeBoard($query)
    {
        return $query->whereIn('type', ['general', 'exam', 'department', 'program', 'academic']);
    }

    public function scopeForNewsEvents($query)
    {
        return $query->whereIn('type', ['news', 'event']);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where(function ($q) use ($departmentId) {
            $q->where('type', 'general')
              ->orWhere(function ($q2) use ($departmentId) {
                  $q2->where('type', 'department')
                     ->where('department_id', $departmentId);
              });
        });
    }

    public function scopeVisibleToDepartmentContext($query, int $departmentId, array $programIds = [])
    {
        return $query->where(function ($q) use ($departmentId, $programIds) {
            $q->whereNull('department_id')
                ->orWhere('department_id', $departmentId);

            if (! empty($programIds)) {
                $q->orWhereIn('program_id', $programIds);
            }
        });
    }

    public function scopeVisibleToStudent($query, Student $student)
    {
        $departmentId = $student->department_id ?: $student->program?->department_id;
        $programId = $student->program_id;
        $semester = $student->current_semester;

        return $query
            ->whereNotIn('type', ['teachers', 'ctevt'])
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
            });
    }
}
