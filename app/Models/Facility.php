<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function getDocumentUrlsAttribute(): array
    {
        return collect($this->documents ?? [])
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function getVideoUrlsAttribute(): array
    {
        return collect($this->videos ?? [])
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }
}
