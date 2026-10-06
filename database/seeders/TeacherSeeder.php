<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Department;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'name' => 'Mr. Hari Prasad Kafle',
                'email' => 'hari.kafle@mtu.edu.np',
                'phone' => '9841234601',
                'department_code' => 'IT',
                'designation' => 'Lecturer',
                'qualification' => 'M.Tech in Computer Science',
                'specialization' => 'Software Engineering',
            ],
            [
                'name' => 'Ms. Anita Gautam',
                'email' => 'anita.gautam@mtu.edu.np',
                'phone' => '9841234602',
                'department_code' => 'IT',
                'designation' => 'Assistant Professor',
                'qualification' => 'M.Tech in Information Technology',
                'specialization' => 'Database Management',
            ],
            [
                'name' => 'Mr. Ravi Sharma',
                'email' => 'ravi.sharma@mtu.edu.np',
                'phone' => '9841234603',
                'department_code' => 'CIVIL',
                'designation' => 'Lecturer',
                'qualification' => 'M.Tech in Civil Engineering',
                'specialization' => 'Structural Analysis',
            ],
            [
                'name' => 'Mr. Sanjay Mishra',
                'email' => 'sanjay.mishra@mtu.edu.np',
                'phone' => '9841234604',
                'department_code' => 'ELECTRICAL',
                'designation' => 'Lecturer',
                'qualification' => 'M.Tech in Electrical Engineering',
                'specialization' => 'Power Systems',
            ],
            [
                'name' => 'Mr. Vijay Kumar Singh',
                'email' => 'vijay.singh@mtu.edu.np',
                'phone' => '9841234605',
                'department_code' => 'MECHANICAL',
                'designation' => 'Lecturer',
                'qualification' => 'M.Tech in Mechanical Engineering',
                'specialization' => 'Thermodynamics',
            ],
        ];

        foreach ($teachers as $teacherData) {
            $department = Department::where('code', $teacherData['department_code'])->first();

            $user = User::withTrashed()->updateOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'phone' => $teacherData['phone'],
                    'gender' => 'Male',
                    'address' => 'Kathmandu, Nepal',
                    'designation' => $teacherData['designation'],
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

            $user->syncRoles(['teacher']);

            // Create teacher profile
            Teacher::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $department?->id,
                    'employee_id' => 'EMP' . $user->id,
                    'designation' => $teacherData['designation'],
                    'qualification' => $teacherData['qualification'],
                    'specialization' => $teacherData['specialization'],
                    'employment_type' => 'Full-time',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Teachers seeded successfully.');
    }
}
