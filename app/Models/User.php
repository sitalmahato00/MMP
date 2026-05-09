<?php

namespace App\Models;

use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'avatar', 'gender', 'dob',
        'address', 'is_active', 'password', 'preferences', 'notification_preferences',
        'two_factor_enabled', 'two_factor_method',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'preferences' => 'array',
            'notification_preferences' => 'array',
        ];
    }

    // ─── Relationships ─────────────────────────────────────

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function parentProfile()
    {
        return $this->hasOne(ParentModel::class);
    }

    public function alumnus()
    {
        return $this->hasOne(Alumni::class);
    }

    public function hodDepartment()
    {
        return $this->hasOne(Department::class, 'hod_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function createdNotices()
    {
        return $this->hasMany(Notice::class, 'created_by');
    }

    public function sentMessages()
    {
        return $this->hasMany(Communication::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Communication::class, 'receiver_id');
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->role($role);
    }

    // ─── Helpers ───────────────────────────────────────────

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? Storage::disk('public')->url($this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff';
    }

    public function isPrincipal(): bool
    {
        return $this->hasRole('principal');
    }

    public function isHod(): bool
    {
        return $this->hasRole('hod');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    public function isAlumni(): bool
    {
        return $this->hasRole('alumni');
    }

    public function primaryRole(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function getPanelType(): string
    {
        return match (true) {
            $this->hasRole('principal') => 'admin',
            $this->hasRole('hod') => 'hod',
            $this->hasRole('teacher') => 'teacher',
            $this->hasRole('student') => 'student',
            $this->hasRole('parent') => 'parent',
            $this->hasRole('alumni') => 'alumni',
            default => 'guest',
        };
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }
}
