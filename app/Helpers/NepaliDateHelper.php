<?php

namespace App\Helpers;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use Carbon\Carbon;

class NepaliDateHelper
{
    /**
     * Convert AD date to BS string.
     *
     * @param  mixed   $date   Carbon instance, DateTimeInterface, or date string
     * @param  string  $format  BS output format (default 'Y-m-d')
     * @return string|null
     */
    public static function toBS($date, string $format = 'Y-m-d'): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            $dateStr = $date instanceof \DateTimeInterface
                ? $date->format('Y-m-d')
                : (string) $date;

            return LaravelNepaliDate::from($dateStr)->toNepaliDate(format: $format, locale: 'en');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Convert BS date string (YYYY-MM-DD) to AD Carbon instance.
     */
    public static function toAD(?string $bsDate): ?Carbon
    {
        if (!$bsDate || trim($bsDate) === '') {
            return null;
        }

        try {
            $adDateStr = LaravelNepaliDate::from(trim($bsDate))->toEnglishDate(format: 'Y-m-d');
            return Carbon::parse($adDateStr);
        } catch (\Throwable) {
            return null;
        }
    }
}
