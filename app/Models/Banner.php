<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['title', 'subtitle', 'image', 'link', 'order', 'is_active', 'button_text', 'button_link'];
    protected $casts = ['is_active' => 'boolean'];
    public function scopeActive($query) { return $query->where('is_active', true)->orderBy('order'); }
}
