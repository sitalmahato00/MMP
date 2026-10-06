<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'slug' => 'information-technology',
                'description' => 'Department of Information Technology',
                'is_active' => true,
            ],
            [
                'name' => 'Civil Engineering',
                'code' => 'CIVIL',
                'slug' => 'civil-engineering',
                'description' => 'Department of Civil Engineering',
                'is_active' => true,
            ],
            [
                'name' => 'Electrical Engineering',
                'code' => 'ELECTRICAL',
                'slug' => 'electrical-engineering',
                'description' => 'Department of Electrical Engineering',
                'is_active' => true,
            ],
            [
                'name' => 'Mechanical Engineering',
                'code' => 'MECHANICAL',
                'slug' => 'mechanical-engineering',
                'description' => 'Department of Mechanical Engineering',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['code' => $department['code']],
                $department
            );
        }

        $this->command->info('Departments seeded successfully.');
    }
}
