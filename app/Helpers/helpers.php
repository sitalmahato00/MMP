<?php

use App\Helpers\NepaliDateHelper;

if (!function_exists('bsDate')) {
    /**
     * Convert an AD date to a BS (Bikram Sambat) formatted string.
     *
     * @param  mixed   $date    Carbon, DateTimeInterface, or date string in AD
     * @param  string  $format  Output format (default 'Y-m-d')
     * @return string
     */
    function bsDate($date, string $format = 'Y-m-d'): string
    {
        return NepaliDateHelper::toBS($date, $format) ?? '';
    }
}

if (!function_exists('adDate')) {
    /**
     * Convert a BS date string (YYYY-MM-DD) to an AD Carbon instance.
     */
    function adDate(?string $bsDate): ?\Carbon\Carbon
    {
        return NepaliDateHelper::toAD($bsDate);
    }
}

if (!function_exists('bsDateTime')) {
    /**
     * Format an AD datetime as BS date plus time.
     *
     * Example output: 2083, Baisakh 06 06:14 PM
     */
    function bsDateTime($date, string $dateFormat = 'Y, F d', string $timeFormat = 'h:i A'): string
    {
        if (! $date) {
            return '';
        }

        $bsPart = bsDate($date, $dateFormat);

        try {
            $timePart = $date instanceof \DateTimeInterface
                ? $date->format($timeFormat)
                : \Carbon\Carbon::parse((string) $date)->format($timeFormat);
        } catch (\Throwable) {
            $timePart = '';
        }

        return trim($bsPart . ' ' . $timePart);
    }
}
