<?php

namespace App\Modules\Parent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'user_id', 'occupation', 'relation_to_student',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function children()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withTimestamps();
    }

    /**
     * Compatibility alias used by admin parents listing.
     */
    public function students()
    {
        return $this->children();
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }
}
