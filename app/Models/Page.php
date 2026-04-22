<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'featured_image',
        'meta_title', 'meta_description', 'is_published',
        'category', 'location', 'capacity', 'availability', 'features',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'capacity' => 'integer',
    ];

    public function scopePublished($query) 
    { 
        return $query->where('is_published', true); 
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
