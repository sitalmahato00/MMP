<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'file_name', 'file_path', 'file_type', 'mime_type',
        'size', 'department_id', 'uploaded_by',
    ];

    public function department() { return $this->belongsTo(Department::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
