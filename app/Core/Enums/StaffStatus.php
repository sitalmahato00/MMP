<?php

namespace App\Core\Enums;

enum StaffStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Resigned = 'resigned';
    case OnLeave  = 'on_leave';
    case Retired  = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Resigned => 'Resigned',
            self::OnLeave  => 'On Leave',
            self::Retired  => 'Retired',
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
