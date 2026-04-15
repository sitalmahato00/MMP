<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Executive extends Model
{
    protected $fillable = [
        'name', 'type', 'designation', 'start_date_bs', 'end_date_bs', 
        'is_current', 'avatar', 'message', 'order'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'order' => 'integer',
    ];

    public function scopePresidents($query)
    {
        return $query->where('type', 'president')->orderBy('order');
    }

    public function scopePrincipals($query)
    {
        return $query->where('type', 'principal')->orderBy('order');
    }
}
