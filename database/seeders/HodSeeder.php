<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;

class HodSeeder extends Seeder
{
    public function run(): void
    {
        $hods = [
            [
                'name' => 'Prof. Ashok Paudel',
                'email' => 'hod.it@mtu.edu.np',
                'phone' => '9841234501',
                'department_code' => 'IT',
                'designation' => 'Head of Department',
            ],
            [
                'name' => 'Prof. Ramesh Baral',
                'email' => 'hod.civil@mtu.edu.np',
                'phone' => '9841234502',
                'department_code' => 'CIVIL',
                'designation' => 'Head of Department',
            ],
            [
                'name' => 'Prof. Sumit Thapa',
                'email' => 'hod.electrical@mtu.edu.np',
                'phone' => '9841234503',
                'department_code' => 'ELECTRICAL',
                'designation' => 'Head of Department',
            ],
            [
                'name' => 'Prof. Deepak Sharma',
                'email' => 'hod.mechanical@mtu.edu.np',
                'phone' => '9841234504',
                'department_code' => 'MECHANICAL',
                'designation' => 'Head of Department',
            ],
        ];

        foreach ($hods as $hodData) {
            $department = Department::where('code', $hodData['department_code'])->first();

            $hod = User::withTrashed()->updateOrCreate(
                ['email' => $hodData['email']],
                [
                    'name' => $hodData['name'],
                    'email' => $hodData['email'],
                    'phone' => $hodData['phone'],
                    'gender' => 'Male',
                    'address' => 'Kathmandu, Nepal',
                    'designation' => $hodData['designation'],
                    'department_id' => $department?->id,
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'two_factor_enabled' => false,
                    'two_factor_method' => 'email',
                ]
            );

            if ($hod->trashed()) {
                $hod->restore();
            }

            $hod->syncRoles(['hod']);
        }

        $this->command->info('HODs seeded successfully.');
    }
}
