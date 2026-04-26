<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\SiteSetting;

class DiagnoseStorage extends Command
{
    protected $signature = 'storage:diagnose';
    protected $description = 'Diagnose storage and logo issues';

    public function handle()
    {
        $this->info('=== Storage Diagnostics ===');
        $this->newLine();

        // Check storage link
        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');
        
        $this->info('1. Storage Symlink Check:');
        if (is_link($publicPath)) {
            $this->info('   ✓ Symlink exists at: ' . $publicPath);
            $this->info('   → Points to: ' . readlink($publicPath));
        } else if (is_dir($publicPath)) {
            $this->warn('   ⚠ Directory exists but is not a symlink');
        } else {
            $this->error('   ✗ Symlink does not exist');
            $this->info('   Run: php artisan storage:link');
        }
        $this->newLine();

        // Check logo file
        $this->info('2. Logo File Check:');
        $logoPath = SiteSetting::where('key', 'site_logo')->value('value');
        
        if ($logoPath) {
            $this->info('   Database logo path: ' . $logoPath);
            
            if (Storage::disk('public')->exists($logoPath)) {
                $this->info('   ✓ Logo file exists');
                $fullPath = Storage::disk('public')->path($logoPath);
                $this->info('   Full path: ' . $fullPath);
                $this->info('   File size: ' . Storage::disk('public')->size($logoPath) . ' bytes');
            } else {
                $this->error('   ✗ Logo file does not exist at: ' . $logoPath);
            }
        } else {
            $this->error('   ✗ No logo path in database');
        }
        $this->newLine();

        // Check APP_URL
        $this->info('3. APP_URL Check:');
        $appUrl = config('app.url');
        $this->info('   Current APP_URL: ' . $appUrl);
        
        if (str_contains($appUrl, 'localhost')) {
            $this->warn('   ⚠ APP_URL is set to localhost - update for production!');
        }
        $this->newLine();

        // Test logo route
        $this->info('4. Logo Route Test:');
        $logoRoute = route('public.brand-logo');
        $this->info('   Logo URL: ' . $logoRoute);
        $this->newLine();

        // Check PWA icon controller
        $this->info('5. PWA Icon Check:');
        $pwaIconUrl = route('pwa.icon', ['size' => 192]);
        $this->info('   PWA Icon URL: ' . $pwaIconUrl);
        $this->newLine();

        $this->info('=== Diagnostics Complete ===');
        
        return 0;
    }
}
