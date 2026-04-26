<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = ['principal', 'hod', 'teacher', 'student', 'parent', 'alumni'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Create one admin user
        $admin = User::withTrashed()->updateOrCreate(
            ['email' => 'sitalmahato077@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'two_factor_enabled' => true,
                'two_factor_method' => 'email',
            ]
        );

        // Restore if soft deleted
        if ($admin->trashed()) {
            $admin->restore();
        }

        // Assign principal role
        $admin->syncRoles(['principal']);

        // Create one active academic session
        AcademicSession::query()->updateOrCreate(
            ['name' => '2081-2082'],
            [
                'name_bs' => '2081-2082',
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->addMonths(10)->toDateString(),
                'is_active' => true,
                'status' => 'active',
                'is_locked' => false,
                'activated_at' => now(),
                'ended_at' => null,
                'notes' => 'Active academic session.',
            ]
        );

        // Seed News & Events - Commented out as seeder doesn't exist
        // $this->call(NewsEventsSeeder::class);

        // Create sample principal
        $principalAvatar = 'executives/principal-avatar.jpg';
        $principalAvatarPath = storage_path('app/public/' . $principalAvatar);
        
        if (!file_exists(dirname($principalAvatarPath))) {
            \Illuminate\Support\Facades\File::makeDirectory(dirname($principalAvatarPath), 0755, true);
        }
        
        // Create a placeholder principal photo
        $principalPhotoData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=');
        file_put_contents($principalAvatarPath, $principalPhotoData);
        
        \App\Models\Executive::updateOrCreate(
            ['type' => 'principal', 'is_current' => true],
            [
                'name' => 'Er. Binay Mahato',
                'type' => 'principal',
                'designation' => 'Principal',
                'start_date_bs' => '2078-01-01',
                'end_date_bs' => null,
                'is_current' => true,
                'avatar' => $principalAvatar,
                'message' => 'Welcome to Manmohan Memorial Polytechnic. We are committed to providing quality technical education and producing skilled professionals who can contribute to the development of our nation.',
                'order' => 1,
            ]
        );

        $this->command->info('Database seeded successfully.');
        $this->command->info('Admin account: sitalmahato077@gmail.com');
        $this->command->info('Password: password');
    }
}
