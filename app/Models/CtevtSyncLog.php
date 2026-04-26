<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CtevtSyncLog extends Model
{
    protected $fillable = [
        'status',
        'notices_added',
        'notices_updated',
        'notices_total',
        'error_message',
        'triggered_by',
        'external_service_ip',
        'duration_seconds',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'notices_added' => 'integer',
        'notices_updated' => 'integer',
        'notices_total' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'success' => '✓ Success',
            'failed' => '✗ Failed',
            'pending' => '⏳ Pending',
            default => $this->status,
        };
    }
}
