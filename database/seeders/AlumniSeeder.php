<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Alumni;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AlumniSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create the program (same one used by StudentSeeder)
        $itDepartment = Department::where('code', 'IT')->first();
        $program = Program::firstOrCreate(
            ['code' => 'B.Tech'],
            [
                'name' => 'Bachelor of Technology',
                'slug' => 'bachelor-of-technology',
                'department_id' => $itDepartment?->id ?? 1,
                'description' => 'Bachelor of Technology Program',
                'total_semesters' => 8,
                'duration_years' => 4,
                'is_active' => true,
            ]
        );

        $alumni = [
            [
                'name' => 'Arjun Poudel',
                'email' => 'alumni@mmp.edu.np',
                'phone' => '9841235001',
                'department_code' => 'IT',
                'graduation_year' => 2020,
                'company' => 'Tech Solutions Nepal',
                'position' => 'Senior Software Engineer',
            ],
            [
                'name' => 'Bindiya Devi',
                'email' => 'alumni2@mmp.edu.np',
                'phone' => '9841235002',
                'department_code' => 'IT',
                'graduation_year' => 2021,
                'company' => 'Digital Innovations',
                'position' => 'Full Stack Developer',
            ],
            [
                'name' => 'Chandra Mohan',
                'email' => 'alumni.civil@mmp.edu.np',
                'phone' => '9841235003',
                'department_code' => 'CIVIL',
                'graduation_year' => 2019,
                'company' => 'Metro Construction',
                'position' => 'Project Manager',
            ],
            [
                'name' => 'Divya Rana',
                'email' => 'alumni.electrical@mmp.edu.np',
                'phone' => '9841235004',
                'department_code' => 'ELECTRICAL',
                'graduation_year' => 2020,
                'company' => 'Nepal Power Systems',
                'position' => 'Electrical Engineer',
            ],
            [
                'name' => 'Esa Thapa',
                'email' => 'alumni.mechanical@mmp.edu.np',
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
                    'gender' => 'Male',
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

            Alumni::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $department?->id,
                    'program_id' => $program->id,
                    'admission_year' => (string) ($alumniData['graduation_year'] - 4),
                    'graduation_year' => (string) $alumniData['graduation_year'],
                    'graduation_date' => now()->setYear($alumniData['graduation_year'])->setMonth(5)->setDay(31),
                    'current_status' => 'employed',
                    'current_job' => $alumniData['position'],
                    'company_name' => $alumniData['company'],
                    'work_location' => 'Kathmandu, Nepal',
                    'employment_status' => 'employed',
                    'is_active' => true,
                    'is_verified' => true,
                ]
            );
        }

        $this->command->info('Alumni seeded successfully.');
        $this->command->info('Primary Alumni Email: alumni@mmp.edu.np | Password: password');
    }
}
