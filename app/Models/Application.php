<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
