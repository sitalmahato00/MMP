<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Department, AcademicSession, Program, Subject};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // 1. Create Active Academic Session
        $session = AcademicSession::create([
            'name' => '2081-2082',
            'name_bs' => '२०८१-२०८२',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(10),
            'is_active' => true,
            'status' => 'active',
        ]);

        // 2. Create Principal (Level 1 Admin)
        $principal = User::create([
            'name' => 'Dr. Principal',
            'email' => 'principal@mmp.edu.np',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $principal->assignRole('principal');

        // 3. Create Department & HOD
        $hodUser = User::create([
            'name' => 'Er. Yubraj Chaudhary',
            'email' => 'hod.it@mmp.edu.np',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $hodUser->assignRole('hod');

        $dept = Department::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'slug' => Str::slug('Information Technology'),
            'hod_id' => $hodUser->id,
            'description' => 'Department of IT',
            'is_active' => true,
        ]);

        // 4. Create Program
        $program = Program::create([
            'department_id' => $dept->id,
            'name' => 'Diploma in Information Technology',
            'code' => 'DIT',
            'slug' => Str::slug('Diploma in Information Technology'),
            'total_semesters' => 6,
            'duration_years' => 3,
            'is_active' => true,
        ]);

        // 5. Create Subjects (from timetable image: Computer Graphics, Web Technology I)
        Subject::create([
            'program_id' => $program->id,
            'semester' => 5,
            'name' => 'Computer Graphics',
            'code' => 'CG501',
            'type' => 'both',
        ]);

        Subject::create([
            'program_id' => $program->id,
            'semester' => 5,
            'name' => 'Web Technology I',
            'code' => 'WT502',
            'type' => 'both',
        ]);
        
        // Output credentials
        $this->command->info('Database seeded successfully.');
        $this->command->info('Principal: principal@mmp.edu.np / password');
        $this->command->info('IT HOD: hod.it@mmp.edu.np / password');
    }
}
