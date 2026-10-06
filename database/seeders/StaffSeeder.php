<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffMembers = [
            [
                'name' => 'Keshav Shumsher',
                'email' => 'staff@mmp.edu.np',
                'phone' => '9841234901',
                'designation' => 'Administrative Officer',
                'department' => 'Administration',
            ],
            [
                'name' => 'Nirupa Adhikari',
                'email' => 'staff2@mmp.edu.np',
                'phone' => '9841234902',
                'designation' => 'Finance Officer',
                'department' => 'Finance',
            ],
            [
                'name' => 'Suresh Tamang',
                'email' => 'staff3@mmp.edu.np',
                'phone' => '9841234903',
                'designation' => 'IT Support',
                'department' => 'IT',
            ],
            [
                'name' => 'Geeta Shrestha',
                'email' => 'staff4@mmp.edu.np',
                'phone' => '9841234904',
                'designation' => 'Librarian',
                'department' => 'Library',
            ],
            [
                'name' => 'Ram Bahadur Limbu',
                'email' => 'staff5@mmp.edu.np',
                'phone' => '9841234905',
                'designation' => 'Security Officer',
                'department' => 'Security',
            ],
        ];

        foreach ($staffMembers as $staffData) {
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $staffData['email']],
                [
                    'name' => $staffData['name'],
                    'email' => $staffData['email'],
                    'phone' => $staffData['phone'],
                    'gender' => 'Male',
                    'address' => 'Kathmandu, Nepal',
                    'designation' => $staffData['designation'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'two_factor_enabled' => false,
                    'two_factor_method' => 'email',
                ]
            );

            if ($user->trashed()) {
                $user->restore();
            }

            $user->syncRoles(['staff']);

            // Staff model does not use SoftDeletes
            Staff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_code' => 'STAFF' . $user->id,
                    'name' => $staffData['name'],
                    'email' => $staffData['email'],
                    'phone' => $staffData['phone'],
                    'designation' => $staffData['designation'],
                    'department' => $staffData['department'],
                    'employment_type' => 'Full-time',
                    'employment_status' => 'active',
                    'join_date' => now()->subYears(rand(1, 10)),
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Staff seeded successfully.');
        $this->command->info('Primary Staff Email: staff@mmp.edu.np | Password: password');
    }
}
