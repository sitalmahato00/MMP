<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $fillable = ['title', 'file_path', 'category', 'department_id'];
    public function department() { return $this->belongsTo(Department::class); }
}
