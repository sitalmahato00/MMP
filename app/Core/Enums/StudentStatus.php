<?php

namespace App\Core\Enums;

enum StudentStatus: string
{
    case Active    = 'active';
    case Inactive  = 'inactive';
    case Graduated = 'graduated';
    case Dropped   = 'dropped';
    case Archived  = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'Active',
            self::Inactive  => 'Inactive',
            self::Graduated => 'Graduated',
            self::Dropped   => 'Dropped Out',
            self::Archived  => 'Archived',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active    => 'badge-success',
            self::Inactive  => 'badge-secondary',
            self::Graduated => 'badge-primary',
            self::Dropped   => 'badge-danger',
            self::Archived  => 'badge-warning',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function validationRule(): string
    {
        return implode(',', self::values());
    }
}
