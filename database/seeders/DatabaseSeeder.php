<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminEmail = 'sitalmahato077@gmail.com';
        $adminPassword = 'password';

        // Create one admin user
        $admin = User::withTrashed()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => bcrypt($adminPassword),
                'email_verified_at' => now(),
                'is_active' => true,
                'two_factor_enabled' => false,
                'two_factor_method' => 'email',
            ]
        );

        // Restore if soft deleted
        if ($admin->trashed()) {
            $admin->restore();
        }

        // Assign principal and admin roles
        $admin->syncRoles(['principal', 'admin']);

        $this->command->info('Database seeded successfully.');
        $this->command->info('Admin account: ' . $admin->email);
        $this->command->info('Password: ' . $adminPassword);
    }
}
