<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'name', 'category', 'department_id', 'program_id',
        'description', 'content', 'images', 'documents', 'videos',
        'capacity', 'location', 'is_published'
    ];

    protected $casts = [
        'images' => 'array',
        'documents' => 'array',
        'videos' => 'array',
        'is_published' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
