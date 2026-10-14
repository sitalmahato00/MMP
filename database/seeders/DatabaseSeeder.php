<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Core setup (order matters)
            RoleSeeder::class,
            DepartmentSeeder::class,

            // Users
            PrincipalSeeder::class,
            HodSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            ParentSeeder::class,
            StaffSeeder::class,
            AlumniSeeder::class,

            // Academic content (depends on users + departments)
            SubjectSeeder::class,
            TimetableSeeder::class,
            ExamMarkSeeder::class,
            AssignmentSeeder::class,
            AttendanceSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ All seeders executed successfully!');
        $this->command->info('');
        $this->command->info('Test Accounts (password: password)');
        $this->command->info('├─ admin@mmp.edu.np         (Admin)');
        $this->command->info('├─ principal@mmp.edu.np     (Principal/Admin)');
        $this->command->info('├─ hod@mmp.edu.np           (Head of Department)');
        $this->command->info('├─ teacher@mmp.edu.np       (Teacher)');
        $this->command->info('├─ student@mmp.edu.np       (Student)');
        $this->command->info('├─ parent@mmp.edu.np        (Parent)');
        $this->command->info('├─ alumni@mmp.edu.np        (Alumni)');
        $this->command->info('└─ staff@mmp.edu.np         (Staff)');
    }
}
