<?php

namespace App\Core\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * HasAuditLog — model trait for automatic audit log entries.
 *
 * Attach this trait to any Eloquent model to enable change tracking.
 *
 * Usage:
 *   class Student extends Model {
 *       use HasAuditLog;
 *   }
 *
 * Logs created / updated / deleted events with before/after states.
 */
trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::created(fn (Model $model) => $model->recordAudit('created', [], $model->getAttributes()));
        static::updated(fn (Model $model) => $model->recordAudit('updated', $model->getOriginal(), $model->getChanges()));
        static::deleted(fn (Model $model) => $model->recordAudit('deleted', $model->getAttributes(), []));
    }

    protected function recordAudit(string $event, array $oldValues, array $newValues): void
    {
        // Guard: AuditLog model must exist and table must be available
        if (! class_exists(AuditLog::class)) {
            return;
        }

        try {
            AuditLog::create([
                'user_id'    => auth()->id(),
                'event'      => $event,
                'auditable_type' => static::class,
                'auditable_id'   => $this->getKey(),
                'old_values' => empty($oldValues) ? null : json_encode($oldValues),
                'new_values' => empty($newValues) ? null : json_encode($newValues),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable) {
            // Never break the main request due to audit failure
        }
    }
}
