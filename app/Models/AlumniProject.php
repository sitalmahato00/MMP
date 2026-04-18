<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniProject extends Model
{
    protected $fillable = [
        'alumni_id', 'type', 'title', 'description', 'supervisor',
        'technologies', 'team_members', 'report_path', 'screenshots',
        'github_url', 'demo_url', 'cover_image', 'status', 'is_visible', 'year',
    ];

    protected $casts = [
        'technologies' => 'array',
        'team_members' => 'array',
        'screenshots' => 'array',
        'is_visible' => 'boolean',
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }
}
