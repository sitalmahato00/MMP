<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PrincipalSeeder extends Seeder
{
    public function run(): void
    {
        $principal = User::withTrashed()->updateOrCreate(
            ['email' => 'principal@mtu.edu.np'],
            [
                'name' => 'Dr. Raj Kumar Singh',
                'email' => 'principal@mtu.edu.np',
                'phone' => '9841234567',
                'gender' => 'Male',
                'address' => 'Kathmandu, Nepal',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'two_factor_enabled' => false,
                'two_factor_method' => 'email',
            ]
        );

        if ($principal->trashed()) {
            $principal->restore();
        }

        $principal->syncRoles(['principal', 'admin']);

        $this->command->info('Principal seeded successfully.');
        $this->command->info('Email: principal@mtu.edu.np | Password: password');
    }
}
