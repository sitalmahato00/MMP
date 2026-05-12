<?php

namespace App\Core\Enums;

enum DownloadVisibility: string
{
    case Public   = 'public';
    case Students = 'students';
    case Private  = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Public   => 'Public',
            self::Students => 'Students Only',
            self::Private  => 'Private',
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
