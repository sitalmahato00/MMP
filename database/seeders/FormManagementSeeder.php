<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FormManagementSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'create-material-requests',
            'edit-material-requests',
            'delete-material-requests',
            'view-material-requests',
            'submit-material-requests',
            'create-repair-orders',
            'edit-repair-orders',
            'delete-repair-orders',
            'view-repair-orders',
            'submit-repair-orders',
            'approve-requests',
            'recommend-requests',
            'reject-requests',
            'view-reports',
            'export-reports',
            'manage-users',
            'manage-departments',
            'manage-settings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles = [
            'department-user',
            'department-head',
            'recommendation-officer',
            'approval-officer',
            'viewer',
        ];

        $rolePermissions = [
            'department-user' => [
                'create-material-requests', 'edit-material-requests',
                'view-material-requests', 'submit-material-requests',
                'create-repair-orders', 'edit-repair-orders',
                'view-repair-orders', 'submit-repair-orders',
            ],
            'department-head' => [
                'create-material-requests', 'edit-material-requests',
                'view-material-requests', 'submit-material-requests',
                'create-repair-orders', 'edit-repair-orders',
                'view-repair-orders', 'submit-repair-orders',
                'recommend-requests', 'view-reports',
            ],
            'recommendation-officer' => [
                'view-material-requests', 'view-repair-orders',
                'recommend-requests', 'reject-requests', 'view-reports',
            ],
            'approval-officer' => [
                'view-material-requests', 'view-repair-orders',
                'approve-requests', 'reject-requests', 'view-reports',
            ],
            'viewer' => [
                'view-material-requests', 'view-repair-orders', 'view-reports',
            ],
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if (isset($rolePermissions[$roleName])) {
                $role->syncPermissions($rolePermissions[$roleName]);
            }
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $dept = Department::first();
        $deptId = $dept?->id;

        User::firstOrCreate(
            ['email' => 'deptuser@mmp.edu.np'],
            [
                'name' => 'Department User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'department_id' => $deptId,
            ]
        )->assignRole('department-user');

        User::firstOrCreate(
            ['email' => 'depthead@mmp.edu.np'],
            [
                'name' => 'Department Head',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'department_id' => $deptId,
                'designation' => 'Department Head',
            ]
        )->assignRole('department-head');

        User::firstOrCreate(
            ['email' => 'recommend@mmp.edu.np'],
            [
                'name' => 'Recommendation Officer',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'designation' => 'Recommendation Officer',
            ]
        )->assignRole('recommendation-officer');

        User::firstOrCreate(
            ['email' => 'approve@mmp.edu.np'],
            [
                'name' => 'Approval Officer',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'designation' => 'Approval Officer',
            ]
        )->assignRole('approval-officer');

        User::firstOrCreate(
            ['email' => 'viewer@mmp.edu.np'],
            [
                'name' => 'Viewer User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        )->assignRole('viewer');

        $this->command->info('Form Management roles and permissions seeded!');
    }
}
