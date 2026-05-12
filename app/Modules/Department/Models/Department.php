<?php

namespace App\Modules\Department\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'slug', 'description', 'photo',
        'seat_capacity', 'hod_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seat_capacity' => 'integer',
    ];

    protected $appends = ['photo_url'];

    // ─── Boot Method ───────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            if (empty($department->slug)) {
                $department->slug = Str::slug($department->name);
                
                // Ensure unique slug
                $originalSlug = $department->slug;
                $count = 1;
                while (static::where('slug', $department->slug)->exists()) {
                    $department->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($department) {
            if ($department->isDirty('name') && empty($department->slug)) {
                $department->slug = Str::slug($department->name);
                
                // Ensure unique slug
                $originalSlug = $department->slug;
                $count = 1;
                while (static::where('slug', $department->slug)->where('id', '!=', $department->id)->exists()) {
                    $department->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    // ─── Relationships ─────────────────────────────────────

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_id');
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function notices()
    {
        return $this->hasMany(Notice::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function alumni()
    {
        return $this->hasMany(Alumni::class);
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}
