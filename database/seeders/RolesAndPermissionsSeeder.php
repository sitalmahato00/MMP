<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Level 1: Highly Sensitive Data (Principal)
        // Level 2: Private Data (HOD, Teacher, Student, Parent)
        // Level 3: Public Data (Public/Guest)

        $roles = [
            'principal', // Super admin
            'hod',       // Department head
            'teacher',
            'student',
            'parent',
            'alumni',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
