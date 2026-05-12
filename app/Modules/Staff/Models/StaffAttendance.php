<?php

namespace App\Modules\Staff\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendances';

    protected $fillable = [
        'staff_id', 'attendance_date', 'status', 'check_in', 'check_out', 'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
