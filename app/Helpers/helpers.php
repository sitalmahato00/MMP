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
