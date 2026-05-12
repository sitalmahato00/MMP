<?php

namespace App\Core\Enums;

enum ExamType: string
{
    case Internal      = 'internal';
    case Final         = 'final';
    case Supplementary = 'supplementary';
    case Practical     = 'practical';
    case Assessment    = 'assessment';

    public function label(): string
    {
        return match ($this) {
            self::Internal      => 'Internal',
            self::Final         => 'Final',
            self::Supplementary => 'Supplementary',
            self::Practical     => 'Practical',
            self::Assessment    => 'Assessment',
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
