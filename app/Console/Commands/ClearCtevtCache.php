<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCtevtCache extends Command
{
    protected $signature = 'cache:clear-ctevt';
    protected $description = 'Clear CTEVT notices cache to fetch fresh data';

    public function handle(): int
    {
        $this->info('Clearing CTEVT notices cache...');

        $cacheKeys = [
            'public:ctevt_notices:general:5',
            'public:ctevt_notices:result:5',
            'public:ctevt_notices:general:6',
            'public:ctevt_notices:result:6',
            'public:ctevt_notices:general:10',
            'public:ctevt_notices:result:10',
            'public:ctevt_result_form',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
            $this->line("✓ Cleared: {$key}");
        }

        $this->newLine();
        $this->info('✓ CTEVT cache cleared successfully!');
        $this->info('Fresh notices will be fetched on next page load.');

        return self::SUCCESS;
    }
}
