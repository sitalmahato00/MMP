<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Staff extends Model
{
    protected $fillable = [
        'user_id', 'name', 'designation', 'department', 
        'email', 'phone', 'photo', 'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return Storage::disk('public')->url($this->photo);
        }
        if ($this->user_id && $this->user->avatar) {
            return $this->user->avatar_url;
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff';
    }
}
