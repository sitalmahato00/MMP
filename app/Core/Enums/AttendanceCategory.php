<?php

namespace App\Core\Enums;

enum AttendanceCategory: string
{
    case Class = 'class';
    case Lab   = 'lab';

    public function label(): string
    {
        return match ($this) {
            self::Class => 'Class',
            self::Lab   => 'Lab',
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
