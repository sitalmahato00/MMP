<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Download extends Model
{
    protected $fillable = [
        'title', 
        'file_path', 
        'file_name', 
        'file_type', 
        'file_size', 
        'description', 
        'category', 
        'department_id', 
        'subject_id',
        'program_id',
        'semester',
        'is_public',
        'visibility',
        'uploaded_by'
    ];
    
    protected $casts = [
        'is_public' => 'boolean'
    ];
    
    public function department() 
    { 
        return $this->belongsTo(Department::class); 
    }
    
    public function subject() 
    { 
        return $this->belongsTo(Subject::class); 
    }
    
    public function program() 
    { 
        return $this->belongsTo(Program::class); 
    }
    
    public function uploader() 
    { 
        return $this->belongsTo(User::class, 'uploaded_by'); 
    }

    public function storageDisk(): string
    {
        return $this->is_public ? 'public' : 'private';
    }

    public function getFileUrlAttribute(): string
    {
        if (! $this->file_path) {
            return '';
        }

        return $this->is_public
            ? Storage::disk('public')->url($this->file_path)
            : route('admin.downloads.file', $this);
    }
}
