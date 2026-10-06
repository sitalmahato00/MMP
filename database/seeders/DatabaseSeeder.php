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
        $this->command->info('├─ principal@mmu.edu.np     (Principal/Admin)');
        $this->command->info('├─ hod@mmp.edu.np           (Head of Department)');
        $this->command->info('├─ teacher@mmu.edu.np       (Teacher)');
        $this->command->info('├─ student@mmu.edu.np       (Student)');
        $this->command->info('├─ parent@mmu.edu.np        (Parent)');
        $this->command->info('├─ alumni@mmu.edu.np        (Alumni)');
        $this->command->info('└─ staff@mmu.edu.np         (Staff)');
    }
}
