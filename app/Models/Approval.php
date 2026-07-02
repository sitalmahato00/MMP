<?php

namespace App\Models;

use App\Helpers\NepaliDateHelper;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'approvable_type', 'approvable_id', 'user_id', 'role',
        'status', 'remarks', 'signature', 'date_bs', 'time', 'ip_address',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->date_bs = $model->date_bs ?? bsDate(now());
            $model->time = $model->time ?? now()->format('H:i:s');
            $model->ip_address = $model->ip_address ?? request()->ip();
        });
    }

    public function approvable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
