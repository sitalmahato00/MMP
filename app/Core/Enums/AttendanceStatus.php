<?php

namespace App\Core\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent  = 'absent';
    case Late    = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent  => 'Absent',
            self::Late    => 'Late',
            self::Excused => 'Excused',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'badge-success',
            self::Absent  => 'badge-danger',
            self::Late    => 'badge-warning',
            self::Excused => 'badge-info',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Returns the comma-separated list for use in validation rules. */
    public static function validationRule(): string
    {
        return implode(',', self::values());
    }
}
