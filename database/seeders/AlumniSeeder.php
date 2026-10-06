<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Alumni;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AlumniSeeder extends Seeder
{
    public function run(): void
    {
        $alumni = [
            [
                'name' => 'Arjun Poudel',
                'email' => 'arjun.poudel@alumni.mtu.edu.np',
                'phone' => '9841235001',
                'department_code' => 'IT',
                'graduation_year' => 2020,
                'company' => 'Tech Solutions Nepal',
                'position' => 'Senior Software Engineer',
            ],
            [
                'name' => 'Bindiya Devi',
                'email' => 'bindiya.devi@alumni.mtu.edu.np',
                'phone' => '9841235002',
                'department_code' => 'IT',
                'graduation_year' => 2021,
                'company' => 'Digital Innovations',
                'position' => 'Full Stack Developer',
            ],
            [
                'name' => 'Chandra Mohan',
                'email' => 'chandra.mohan@alumni.mtu.edu.np',
                'phone' => '9841235003',
                'department_code' => 'CIVIL',
                'graduation_year' => 2019,
                'company' => 'Metro Construction',
                'position' => 'Project Manager',
            ],
            [
                'name' => 'Divya Rana',
                'email' => 'divya.rana@alumni.mtu.edu.np',
                'phone' => '9841235004',
                'department_code' => 'ELECTRICAL',
                'graduation_year' => 2020,
                'company' => 'Nepal Power Systems',
                'position' => 'Electrical Engineer',
            ],
            [
                'name' => 'Esa Thapa',
                'email' => 'esa.thapa@alumni.mtu.edu.np',
                'phone' => '9841235005',
                'department_code' => 'MECHANICAL',
                'graduation_year' => 2021,
                'company' => 'Industrial Manufacturing Co.',
                'position' => 'Design Engineer',
            ],
        ];

        foreach ($alumni as $alumniData) {
            $department = Department::where('code', $alumniData['department_code'])->first();

            $user = User::withTrashed()->updateOrCreate(
                ['email' => $alumniData['email']],
                [
                    'name' => $alumniData['name'],
                    'email' => $alumniData['email'],
                    'phone' => $alumniData['phone'],
                    'gender' => strpos($alumniData['name'], 'a') !== false ? 'Female' : 'Male',
                    'address' => 'Kathmandu, Nepal',
                    'department_id' => $department?->id,
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

            $user->syncRoles(['alumnus']);

            // Create alumni profile
            Alumni::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $department?->id,
                    'admission_year' => $alumniData['graduation_year'] - 4,
                    'graduation_year' => $alumniData['graduation_year'],
                    'graduation_date' => now()->setYear($alumniData['graduation_year'])->setMonth(5)->setDay(31),
                    'current_status' => 'Employed',
                    'current_job' => $alumniData['position'],
                    'company_name' => $alumniData['company'],
                    'work_location' => 'Kathmandu, Nepal',
                    'employment_status' => 'employed',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Alumni seeded successfully.');
    }
}
