<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StaffDocument extends Model
{
    protected $fillable = [
        'staff_id', 'document_type', 'label', 'file_path', 'mime_type', 'file_size',
        'issued_at', 'is_public', 'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'is_public' => 'boolean',
        'file_size' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'citizenship' => 'Citizenship',
            'appointment_letter' => 'Appointment Letter',
            'contract' => 'Contract Agreement',
            'id_card' => 'Staff ID Card',
            'certification' => 'Certification',
            default => 'Document',
        };
    }
}
