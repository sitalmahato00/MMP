<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create or get department
        $dept = Department::firstOrCreate(
            ['code' => 'CE'],
            [
                'name' => 'Computer Engineering',
                'slug' => 'computer-engineering',
                'is_active' => true,
            ]
        );

        // Create or get program
        $program = Program::firstOrCreate(
            ['code' => 'DCE', 'department_id' => $dept->id],
            [
                'name' => 'Diploma in Computer Engineering',
                'slug' => 'diploma-computer-engineering',
                'affiliation_type' => 'ctevt',
                'total_semesters' => 6,
                'duration_years' => 3,
                'is_active' => true,
            ]
        );

        // Get active session
        $session = AcademicSession::where('is_active', true)->first();

        // Create HOD user
        $hodUser = User::firstOrCreate(
            ['email' => 'hod@test.com'],
            [
                'name' => 'HOD User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        if (!$hodUser->hasRole('hod')) {
            $hodUser->assignRole('hod');
        }
        $dept->update(['hod_id' => $hodUser->id]);
        Teacher::firstOrCreate(
            ['user_id' => $hodUser->id],
            [
                'department_id' => $dept->id,
                'employee_id' => 'T001',
                'designation' => 'HOD',
                'qualification' => 'M.Tech',
                'join_date' => now(),
                'is_active' => true,
            ]
        );

        // Create Teacher user
        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@test.com'],
            [
                'name' => 'Teacher User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        if (!$teacherUser->hasRole('teacher')) {
            $teacherUser->assignRole('teacher');
        }
        Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'department_id' => $dept->id,
                'employee_id' => 'T002',
                'designation' => 'Lecturer',
                'qualification' => 'B.Tech',
                'join_date' => now(),
                'is_active' => true,
            ]
        );

        // Create Student user
        $studentUser = User::firstOrCreate(
            ['email' => 'student@test.com'],
            [
                'name' => 'Student User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        if (!$studentUser->hasRole('student')) {
            $studentUser->assignRole('student');
        }
        $student = Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'department_id' => $dept->id,
                'program_id' => $program->id,
                'academic_session_id' => $session->id,
                'student_no' => 'S001',
                'registration_number' => 'REG001',
                'batch' => '2081',
                'current_semester' => 1,
                'section' => 'A',
                'status' => 'active',
                'admission_date' => now(),
            ]
        );

        // Create Parent user
        $parentUser = User::firstOrCreate(
            ['email' => 'parent@test.com'],
            [
                'name' => 'Parent User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        if (!$parentUser->hasRole('parent')) {
            $parentUser->assignRole('parent');
        }
        $parent = ParentModel::firstOrCreate(
            ['user_id' => $parentUser->id],
            [
                'relation_to_student' => 'father',
                'occupation' => 'Business',
            ]
        );
        // Link parent to student if not already linked
        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            $parent->students()->attach($student->id);
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('HOD: hod@test.com / password');
        $this->command->info('Teacher: teacher@test.com / password');
        $this->command->info('Student: student@test.com / password');
        $this->command->info('Parent: parent@test.com / password');
    }
}
