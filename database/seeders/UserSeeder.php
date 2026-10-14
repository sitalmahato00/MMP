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
        // 1. Reset cached roles & ensure roles exist
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

        // 2. Safely resolve existing Production Academic Session, Department & Program
        // Look for existing active data first; only create fallback if tables are completely empty.
        $academicSession = AcademicSession::where('is_active', true)->first()
            ?? AcademicSession::latest()->first()
            ?? AcademicSession::create([
                'name' => date('Y') . '/' . (date('Y') + 1),
                'start_date' => date('Y-01-01'),
                'end_date' => date('Y-12-31'),
                'is_active' => true,
                'status' => 'active',
            ]);

        $department = Department::where('code', 'IT')->first()
            ?? Department::first()
            ?? Department::create([
                'name' => 'Information Technology',
                'code' => 'IT',
                'slug' => 'information-technology',
                'description' => 'Department of Information Technology',
                'is_active' => true,
            ]);

        $program = Program::where('department_id', $department->id)->first()
            ?? Program::first()
            ?? Program::create([
                'name' => 'Bachelor of Technology',
                'code' => 'B.Tech',
                'slug' => 'bachelor-of-technology',
                'department_id' => $department->id,
                'total_semesters' => 8,
                'duration_years' => 4,
                'is_active' => true,
            ]);

        $defaultPassword = Hash::make('password');

        /**
         * Helper: Create or safely update user without overwriting existing production password.
         */
        $findOrCreateSafeUser = function (string $email, string $name, array $userRoles, ?int $deptId = null) use ($defaultPassword) {
            $user = User::withTrashed()->where('email', $email)->first();

            if (!$user) {
                // New user: set default password
                $user = User::create([
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
                ]);
            } else {
                // Existing user: restore if deleted, update missing fields, but DO NOT overwrite real password!
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->update([
                    'is_active' => true,
                    'department_id' => $user->department_id ?? $deptId,
                ]);
            }

            $user->syncRoles($userRoles);

            return $user;
        };

        // 3. Create or Link Accounts
        
        // Admin
        $findOrCreateSafeUser('admin@mmp.edu.np', 'System Administrator', ['admin']);

        // Principal
        $findOrCreateSafeUser('principal@mmp.edu.np', 'College Principal', ['principal', 'admin']);

        // HOD
        $hod = $findOrCreateSafeUser('hod@mmp.edu.np', 'Head of Department (IT)', ['hod'], $department->id);
        if (!$department->hod_id) {
            $department->update(['hod_id' => $hod->id]);
        }

        // Teacher
        $teacherUser = $findOrCreateSafeUser('teacher@mmp.edu.np', 'IT Lecturer', ['teacher'], $department->id);
        Teacher::withTrashed()->firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'department_id' => $department->id,
                'employee_id' => 'EMP-TEST-' . $teacherUser->id,
                'designation' => 'Lecturer',
                'qualification' => 'M.Tech in Computer Science',
                'specialization' => 'Software Engineering',
                'employment_type' => 'permanent',
                'is_active' => true,
            ]
        );

        // Student
        $studentUser = $findOrCreateSafeUser('student@mmp.edu.np', 'College Student', ['student'], $department->id);
        $student = Student::withTrashed()->firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'department_id' => $department->id,
                'program_id' => $program->id,
                'academic_session_id' => $academicSession->id,
                'student_no' => 'DEMO-STU-' . $studentUser->id,
                'registration_number' => 'DEMO-REG-' . $studentUser->id,
                'current_semester' => 1,
                'semester' => 1,
                'batch' => (string) date('Y'),
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
        $parentUser = $findOrCreateSafeUser('parent@mmp.edu.np', 'Student Parent', ['parent']);
        $parentProfile = ParentModel::firstOrCreate(
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
        $alumniUser = $findOrCreateSafeUser('alumni@mmp.edu.np', 'College Alumni', ['alumni']);
        Alumni::withTrashed()->firstOrCreate(
            ['user_id' => $alumniUser->id],
            [
                'department_id' => $department->id,
                'program_id' => $program->id,
                'graduation_year' => (string) (date('Y') - 1),
                'current_status' => 'employed',
                'current_job' => 'Software Engineer',
                'company_name' => 'Tech Solutions Nepal',
                'employment_status' => 'employed',
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        // Staff
        $staffUser = $findOrCreateSafeUser('staff@mmp.edu.np', 'Administrative Staff', ['staff']);
        Staff::firstOrCreate(
            ['user_id' => $staffUser->id],
            [
                'staff_code' => 'STAFF-TEST-' . $staffUser->id,
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
        $findOrCreateSafeUser('executive@mmp.edu.np', 'Executive Board Member', ['executive']);

        // Visitor
        $findOrCreateSafeUser('visitor@mmp.edu.np', 'Campus Visitor', ['visitor']);

        $this->command->info('---------------------------------------------------------');
        $this->command->info(' Role Accounts Verified & Ready (Production-Safe)');
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
