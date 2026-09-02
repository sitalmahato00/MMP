<?php

namespace App\Models;

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
        'is_popup', 'popup_from_bs', 'popup_to_bs', 'popup_from', 'popup_to',
        'main_site_requested', 'main_site_status', 'request_as_popup', 'request_note',
        'main_site_approved_at', 'main_site_approved_by',
    ];

    protected $casts = [
        'semester' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'is_popup' => 'boolean',
        'popup_from' => 'date',
        'popup_to' => 'date',
        'main_site_requested' => 'boolean',
        'request_as_popup' => 'boolean',
        'main_site_approved_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mainSiteApprovedBy()
    {
        return $this->belongsTo(User::class, 'main_site_approved_by');
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

    public function scopePendingMainSiteRequest($query)
    {
        return $query->where('main_site_requested', true)
            ->where('main_site_status', 'pending');
    }

    public function scopeApprovedForMainSite($query)
    {
        return $query->where('main_site_status', 'approved');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActivePopup($query)
    {
        $today = now()->toDateString();

        return $query->where('is_published', true)
            ->where('is_popup', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('popup_from')
                  ->orWhere('popup_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('popup_to')
                  ->orWhere('popup_to', '>=', $today);
            });
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

    public function getCoverImageUrlAttribute(): ?string
    {
        $imageAttachment = $this->attachments?->firstWhere('is_image', true);
        if ($imageAttachment) {
            return $imageAttachment->url;
        }

        if ($this->attachment && preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $this->attachment)) {
            return asset('storage/' . $this->attachment);
        }

        return null;
    }

    public function getGalleryImagesAttribute()
    {
        return $this->attachments?->filter(fn($a) => $a->is_image) ?? collect();
    }
}
