<?php

namespace App\Core\Enums;

enum BloodGroup: string
{
    case APos  = 'A+';
    case ANeg  = 'A-';
    case BPos  = 'B+';
    case BNeg  = 'B-';
    case OPos  = 'O+';
    case ONeg  = 'O-';
    case ABPos = 'AB+';
    case ABNeg = 'AB-';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function validationRule(): string
    {
        return implode(',', self::values());
    }
}
