<?php

namespace App\Modules\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSessionSemester extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_session_id',
        'semester_number',
        'start_date',
        'end_date',
        'status',
        'delay_reason',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }
}
