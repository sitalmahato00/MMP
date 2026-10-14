<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Department;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles & ensure all roles exist
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'admin',
            'principal',
            'hod',
            'teacher',
            'student',
            'parent',
            'alumni',
            'alumnus',
            'staff',
            'executive',
            'visitor',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 2. Ensure baseline department, program, and academic session exist
        $academicSession = AcademicSession::firstOrCreate(
            ['name' => '2024/2025'],
            [
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        $department = Department::firstOrCreate(
            ['code' => 'IT'],
            [
                'name' => 'Information Technology',
                'slug' => 'information-technology',
                'description' => 'Department of Information Technology',
                'is_active' => true,
            ]
        );

        $program = Program::firstOrCreate(
            ['code' => 'B.Tech'],
            [
                'name' => 'Bachelor of Technology',
                'slug' => 'bachelor-of-technology',
                'department_id' => $department->id,
                'description' => 'Bachelor of Technology Program',
                'total_semesters' => 8,
                'duration_years' => 4,
                'is_active' => true,
            ]
        );

        $defaultPassword = Hash::make('password');

        // Helper to update or create user
        $createUser = function (string $email, string $name, array $userRoles, ?int $deptId = null) use ($defaultPassword) {
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email' => $email,
                    'phone' => '9841000000',
                    'gender' => 'male',
                    'address' => 'Kathmandu, Nepal',
                    'department_id' => $deptId,
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'two_factor_enabled' => false,
                    'two_factor_method' => 'email',
                ]
            );

            if ($user->trashed()) {
                $user->restore();
            }

            $user->syncRoles($userRoles);

            return $user;
        };

        // 3. Create all role accounts
        // Admin
        $createUser('admin@mmp.edu.np', 'System Administrator', ['admin']);

        // Principal
        $createUser('principal@mmp.edu.np', 'College Principal', ['principal', 'admin']);

        // HOD
        $hod = $createUser('hod@mmp.edu.np', 'Head of Department (IT)', ['hod'], $department->id);
        $department->update(['hod_id' => $hod->id]);

        // Teacher
        $teacherUser = $createUser('teacher@mmp.edu.np', 'IT Lecturer', ['teacher'], $department->id);
        Teacher::withTrashed()->updateOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'department_id' => $department->id,
                'employee_id' => 'EMP' . $teacherUser->id,
                'designation' => 'Lecturer',
                'qualification' => 'M.Tech in Computer Science',
                'specialization' => 'Software Engineering',
                'employment_type' => 'permanent',
                'is_active' => true,
            ]
        );

        // Student
        $studentUser = $createUser('student@mmp.edu.np', 'College Student', ['student'], $department->id);
        $student = Student::withTrashed()->updateOrCreate(
            ['user_id' => $studentUser->id],
            [
                'department_id' => $department->id,
                'program_id' => $program->id,
                'academic_session_id' => $academicSession->id,
                'student_no' => 'STU-001',
                'registration_number' => 'REG-001',
                'current_semester' => 1,
                'semester' => 1,
                'batch' => '2024',
                'section' => 'A',
                'admission_date' => now(),
                'guardian_name' => 'Student Guardian',
                'guardian_phone' => '9841000000',
                'blood_group' => 'A+',
                'status' => 'active',
                'is_active' => true,
            ]
        );

        // Parent
        $parentUser = $createUser('parent@mmp.edu.np', 'Student Parent', ['parent']);
        $parentProfile = ParentModel::updateOrCreate(
            ['user_id' => $parentUser->id],
            [
                'occupation' => 'Engineer',
                'relation_to_student' => 'Father',
            ]
        );
        if ($student) {
            $parentProfile->children()->syncWithoutDetaching([$student->id]);
        }

        // Alumni
        $createUser('alumni@mmp.edu.np', 'College Alumni', ['alumni']);
        Alumni::withTrashed()->updateOrCreate(
            ['user_id' => User::where('email', 'alumni@mmp.edu.np')->value('id')],
            [
                'department_id' => $department->id,
                'program_id' => $program->id,
                'graduation_year' => '2023',
                'current_status' => 'employed',
                'current_job' => 'Software Engineer',
                'company_name' => 'Tech Solutions Nepal',
                'employment_status' => 'employed',
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        // Staff
        $staffUser = $createUser('staff@mmp.edu.np', 'Administrative Staff', ['staff']);
        Staff::updateOrCreate(
            ['user_id' => $staffUser->id],
            [
                'staff_code' => 'STAFF' . $staffUser->id,
                'name' => $staffUser->name,
                'email' => $staffUser->email,
                'phone' => $staffUser->phone,
                'designation' => 'Administrative Officer',
                'department' => 'Administration',
                'employment_type' => 'Full-time',
                'employment_status' => 'active',
                'is_active' => true,
            ]
        );

        // Executive
        $createUser('executive@mmp.edu.np', 'Executive Board Member', ['executive']);

        // Visitor
        $createUser('visitor@mmp.edu.np', 'Campus Visitor', ['visitor']);

        $this->command->info('---------------------------------------------------------');
        $this->command->info(' All Role Accounts Created Successfully! (Password: password)');
        $this->command->info('---------------------------------------------------------');
        $accounts = [
            ['Role' => 'admin',     'Email' => 'admin@mmp.edu.np',     'Panel' => '/admin/dashboard'],
            ['Role' => 'principal', 'Email' => 'principal@mmp.edu.np', 'Panel' => '/admin/dashboard'],
            ['Role' => 'hod',       'Email' => 'hod@mmp.edu.np',       'Panel' => '/hod/dashboard'],
            ['Role' => 'teacher',   'Email' => 'teacher@mmp.edu.np',   'Panel' => '/teacher/dashboard'],
            ['Role' => 'student',   'Email' => 'student@mmp.edu.np',   'Panel' => '/student/dashboard'],
            ['Role' => 'parent',    'Email' => 'parent@mmp.edu.np',    'Panel' => '/parent/dashboard'],
            ['Role' => 'alumni',    'Email' => 'alumni@mmp.edu.np',    'Panel' => '/alumni/dashboard'],
            ['Role' => 'staff',     'Email' => 'staff@mmp.edu.np',     'Panel' => '/staff'],
            ['Role' => 'executive', 'Email' => 'executive@mmp.edu.np', 'Panel' => '/home'],
            ['Role' => 'visitor',   'Email' => 'visitor@mmp.edu.np',   'Panel' => '/home'],
        ];

        $this->command->table(['Role', 'Email', 'Dashboard / Target'], $accounts);
    }
}
