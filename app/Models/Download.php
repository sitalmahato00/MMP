<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $fillable = ['title', 'file_path', 'file_name', 'file_type', 'file_size', 'description', 'category', 'department_id', 'is_public', 'uploaded_by'];
    protected $casts = ['is_public' => 'boolean'];
    public function department() { return $this->belongsTo(Department::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function storageDisk(): string
    {
        return $this->is_public ? 'public' : 'local';
    }
}
