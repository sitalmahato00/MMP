<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'staff_code', 'name', 'designation', 'department',
        'email', 'phone', 'address', 'dob', 'gender', 'photo',
        'employment_type', 'employment_status', 'join_date', 'end_date',
        'salary_amount', 'working_schedule', 'assigned_roles', 'responsibilities',
        'bio', 'public_visible', 'featured', 'show_email_public', 'show_phone_public',
        'order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'public_visible' => 'boolean',
        'featured' => 'boolean',
        'show_email_public' => 'boolean',
        'show_phone_public' => 'boolean',
        'join_date' => 'date',
        'end_date' => 'date',
        'dob' => 'date',
        'working_schedule' => 'array',
        'assigned_roles' => 'array',
        'responsibilities' => 'array',
        'salary_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(StaffDocument::class)->latest();
    }

    public function attendanceRecords()
    {
        return $this->hasMany(StaffAttendance::class)->orderByDesc('attendance_date');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return publicStorageUrl($this->photo) ?? $this->user?->avatar_url;
        }

        if ($this->user?->avatar) {
            return $this->user->avatar_url;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff';
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopePublicVisible($query)
    {
        return $query->where('public_visible', true);
    }

    public function scopeEmployed($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->assigned_roles[0] ?? $this->designation;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->employment_status) {
            'active' => 'Active',
            'leave' => 'On Leave',
            'resigned' => 'Resigned',
            default => 'Unknown',
        };
    }
}
