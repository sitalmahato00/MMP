<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Department;
use App\Models\Program;
use App\Models\AcademicSession;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Create or get current academic session
        $academicSession = AcademicSession::firstOrCreate(
            ['name' => '2024/2025'],
            [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'is_active' => true,
            ]
        );

        // Get or create a program (must include department_id and slug)
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

        $students = [
            [
                'name' => 'Aarav Paudel',
                'email' => 'student@mmp.edu.np',
                'phone' => '9841234701',
                'department_code' => 'IT',
                'student_no' => 'STU001',
                'registration_number' => 'REG001',
                'current_semester' => 1,
                'batch' => 2024,
            ],
            [
                'name' => 'Bhavna Sharma',
                'email' => 'student2@mmp.edu.np',
                'phone' => '9841234702',
                'department_code' => 'IT',
                'student_no' => 'STU002',
                'registration_number' => 'REG002',
                'current_semester' => 1,
                'batch' => 2024,
            ],
            [
                'name' => 'Chetan Niroula',
                'email' => 'student.civil@mmp.edu.np',
                'phone' => '9841234703',
                'department_code' => 'CIVIL',
                'student_no' => 'STU003',
                'registration_number' => 'REG003',
                'current_semester' => 1,
                'batch' => 2024,
            ],
            [
                'name' => 'Deepika Baniya',
                'email' => 'student.electrical@mmp.edu.np',
                'phone' => '9841234704',
                'department_code' => 'ELECTRICAL',
                'student_no' => 'STU004',
                'registration_number' => 'REG004',
                'current_semester' => 1,
                'batch' => 2024,
            ],
            [
                'name' => 'Emad Khan',
                'email' => 'student.mechanical@mmp.edu.np',
                'phone' => '9841234705',
                'department_code' => 'MECHANICAL',
                'student_no' => 'STU005',
                'registration_number' => 'REG005',
                'current_semester' => 1,
                'batch' => 2024,
            ],
        ];

        foreach ($students as $studentData) {
            $department = Department::where('code', $studentData['department_code'])->first();

            $user = User::withTrashed()->updateOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'email' => $studentData['email'],
                    'phone' => $studentData['phone'],
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

            $user->syncRoles(['student']);

            Student::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $department?->id,
                    'program_id' => $program->id,
                    'academic_session_id' => $academicSession->id,
                    'student_no' => $studentData['student_no'],
                    'registration_number' => $studentData['registration_number'],
                    'current_semester' => $studentData['current_semester'],
                    'batch' => $studentData['batch'],
                    'section' => 'A',
                    'admission_date' => now(),
                    'guardian_name' => $studentData['name'] . ' Guardian',
                    'guardian_phone' => $studentData['phone'],
                    'blood_group' => 'A+',
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('Students seeded successfully.');
        $this->command->info('Primary Student Email: student@mmp.edu.np | Password: password');
    }
}
