<?php

namespace App\Modules\Notification\Models;


use App\Modules\Academic\Models\Subject;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'subject', 'message', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }

    public function scopeUnread($query) { return $query->where('is_read', false); }
}
