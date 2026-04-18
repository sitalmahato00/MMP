<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniAchievement extends Model
{
    protected $fillable = [
        'alumni_id', 'title', 'description', 'certificate_path', 'year',
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }
}
