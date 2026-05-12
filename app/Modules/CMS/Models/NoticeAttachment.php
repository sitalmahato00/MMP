<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeAttachment extends Model
{
    protected $fillable = ['notice_id', 'file_path', 'file_name', 'file_type', 'file_size'];

    public function notice()
    {
        return $this->belongsTo(Notice::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_type) === 'pdf';
    }
}
