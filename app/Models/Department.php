<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'slug', 'description', 'photo',
        'syllabus', 'seat_capacity', 'hod_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seat_capacity' => 'integer',
    ];

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
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function getSyllabusUrlAttribute(): ?string
    {
        return $this->syllabus ? asset('storage/' . $this->syllabus) : null;
    }
}
