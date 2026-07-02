<?php

namespace App\Models;

use App\Helpers\NepaliDateHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'repair_number', 'date_bs', 'date_ad', 'user_id',
        'department_id', 'equipment_name', 'problem_description',
        'estimated_cost', 'approved_cost', 'status', 'remarks',
    ];

    protected $casts = [
        'date_ad' => 'date',
        'estimated_cost' => 'decimal:2',
        'approved_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->repair_number)) {
                $model->repair_number = static::generateRepairNumber();
            }
            if (empty($model->date_ad) && !empty($model->date_bs)) {
                $model->date_ad = NepaliDateHelper::toAD($model->date_bs);
            }
            if (empty($model->status)) {
                $model->status = 'draft';
            }
        });
    }

    public static function generateRepairNumber(): string
    {
        $prefix = 'RO-' . bsDate(now(), 'Y') . '-';
        $last = static::where('repair_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('repair_number');
        $num = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('repair_number', 'like', "%{$search}%")
              ->orWhere('equipment_name', 'like', "%{$search}%")
              ->orWhere('remarks', 'like', "%{$search}%");
        });
    }
}
