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

        $this->command->info('Database seeded successfully.');
        $this->command->info('Admin account: sitalmahato077@gmail.com');
        $this->command->info('Password: password');
    }
}
