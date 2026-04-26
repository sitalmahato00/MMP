<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;

class DiagnoseHodAvatar extends Command
{
    protected $signature = 'hod:diagnose-avatar {department_id}';
    protected $description = 'Diagnose HOD avatar issues for a specific department';

    public function handle()
    {
        $departmentId = $this->argument('department_id');
        
        $department = Department::with('hod')->find($departmentId);
        
        if (!$department) {
            $this->error('Department not found');
            return 1;
        }
        
        $this->info('=== HOD Avatar Diagnostics ===');
        $this->newLine();
        
        $this->info('Department: ' . $department->name);
        $this->info('Department ID: ' . $department->id);
        $this->newLine();
        
        if (!$department->hod) {
            $this->error('No HOD assigned to this department');
            return 1;
        }
        
        $hod = $department->hod;
        
        $this->info('HOD Name: ' . $hod->name);
        $this->info('HOD ID: ' . $hod->id);
        $this->info('HOD Email: ' . $hod->email);
        $this->newLine();
        
        $this->info('Avatar field value: ' . ($hod->avatar ?: 'NULL'));
        $this->newLine();
        
        if ($hod->avatar) {
            $this->info('Checking if avatar file exists...');
            
            if (Storage::disk('public')->exists($hod->avatar)) {
                $this->info('✓ Avatar file EXISTS');
                $this->info('  Full path: ' . Storage::disk('public')->path($hod->avatar));
                $this->info('  File size: ' . Storage::disk('public')->size($hod->avatar) . ' bytes');
                $this->info('  URL: ' . Storage::disk('public')->url($hod->avatar));
            } else {
                $this->error('✗ Avatar file DOES NOT EXIST at: ' . $hod->avatar);
            }
        } else {
            $this->warn('No avatar set in database - will use fallback');
        }
        
        $this->newLine();
        $this->info('Avatar URL accessor result: ' . $hod->avatar_url);
        $this->newLine();
        
        $this->info('Storage symlink check:');
        $publicPath = public_path('storage');
        if (is_link($publicPath)) {
            $this->info('✓ Symlink exists at: ' . $publicPath);
            $this->info('  Points to: ' . readlink($publicPath));
        } else if (is_dir($publicPath)) {
            $this->warn('⚠ Directory exists but is not a symlink');
        } else {
            $this->error('✗ Symlink does not exist');
            $this->info('  Run: php artisan storage:link');
        }
        
        return 0;
    }
}
