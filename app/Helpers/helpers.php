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

if (!function_exists('logoVersion')) {
    /**
     * Get a cache-busting version parameter for the site logo.
     * Returns the last modified timestamp of the logo file.
     *
     * @return string
     */
    function logoVersion(): string
    {
        return \Illuminate\Support\Facades\Cache::remember('brand:logo_version', 600, function () {
            try {
                $logoPath = \App\Models\SiteSetting::query()
                    ->where('key', 'site_logo')
                    ->value('value');
                
                if ($logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)) {
                    return (string) \Illuminate\Support\Facades\Storage::disk('public')->lastModified($logoPath);
                }
            } catch (\Throwable) {
                // Fallback to current time if there's any error
            }
            
            return (string) time();
        });
    }
}
if (!function_exists('publicStorageUrl')) {
    /**
     * Generate a public storage URL for a local public disk path.
     */
    function publicStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
