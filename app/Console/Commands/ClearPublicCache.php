<?php

namespace App\Console\Commands;

use App\Services\PublicDataService;
use Illuminate\Console\Command;

class ClearPublicCache extends Command
{
    protected $signature = 'cache:clear-public';
    protected $description = 'Clear all public-facing caches (homepage, notices, departments, etc.)';

    public function handle(): int
    {
        $this->info('Clearing public caches...');
        
        PublicDataService::invalidate('*');
        
        $this->info('✓ Public caches cleared successfully!');
        $this->info('');
        $this->info('Cleared caches:');
        $this->line('  - Homepage data');
        $this->line('  - Notices and news');
        $this->line('  - Departments');
        $this->line('  - Downloads');
        $this->line('  - All other public pages');
        
        return Command::SUCCESS;
    }
}
