<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumni extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alumni';

    protected $fillable = [
        'user_id', 'student_id', 'department_id', 'program_id',
        'roll_number', 'admission_year', 'graduation_year', 'graduation_date',
        'current_status', 'current_job', 'company_name', 'work_location', 'employment_status',
        'achievements', 'bio', 'skills',
        'linkedin_url', 'github_url', 'portfolio_url', 'cv_path',
        'profile_completion', 'visibility',
        'is_featured', 'is_active', 'is_verified',
    ];

    protected $casts = [
        'graduation_date' => 'date',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'profile_completion' => 'integer',
        'skills' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function projects()
    {
        return $this->hasMany(AlumniProject::class);
    }

    public function minorProject()
    {
        return $this->hasOne(AlumniProject::class)->where('type', 'minor');
    }

    public function majorProject()
    {
        return $this->hasOne(AlumniProject::class)->where('type', 'major');
    }

    public function achievementRecords()
    {
        return $this->hasMany(AlumniAchievement::class);
    }

    public function employmentHistory()
    {
        return $this->hasMany(AlumniEmployment::class)->orderByDesc('start_date');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePublicVisible($query)
    {
        return $query->where('visibility', 'public');
    }

    public function getFullNameAttribute()
    {
        return $this->user?->name;
    }

    public function calculateProfileCompletion(): int
    {
        $fields = [
            !empty($this->user?->name),
            !empty($this->user?->email),
            !empty($this->user?->phone),
            !empty($this->user?->avatar),
            !empty($this->bio),
            !empty($this->current_job),
            !empty($this->company_name),
            !empty($this->linkedin_url),
            !empty($this->skills) && count($this->skills) > 0,
            $this->projects()->exists(),
        ];

        $filled = collect($fields)->filter()->count();
        return (int) round(($filled / count($fields)) * 100);
    }
}
