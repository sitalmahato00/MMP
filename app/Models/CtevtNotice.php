<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CtevtNotice extends Model
{
    protected $fillable = [
        'type',
        'external_id',
        'title',
        'url',
        'updated_date',
        'publisher',
        'files_count',
        'raw_data',
        'fetched_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'fetched_at' => 'datetime',
        'files_count' => 'integer',
    ];

    public function scopeGeneral($query)
    {
        return $query->where('type', 'general');
    }

    public function scopeResult($query)
    {
        return $query->where('type', 'result');
    }

    public function scopeRecent($query, int $limit = 6)
    {
        return $query->latest('fetched_at')->limit($limit);
    }
}
