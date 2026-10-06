<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Program;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $program = Program::where('code', 'B.Tech')->first();
        if (!$program) {
            $this->command->warn('No B.Tech program found. Run StudentSeeder first.');
            return;
        }

        $subjects = [
            // Semester 1
            ['name' => 'English I',            'code' => 'ENG101', 'semester' => 1, 'type' => 'theory'],
            ['name' => 'Mathematics I',        'code' => 'MTH101', 'semester' => 1, 'type' => 'theory'],
            ['name' => 'Computer Fundamentals','code' => 'CFN101', 'semester' => 1, 'type' => 'both'],
            ['name' => 'Programming in C',     'code' => 'PGC101', 'semester' => 1, 'type' => 'both'],
            // Semester 2
            ['name' => 'English II',           'code' => 'ENG201', 'semester' => 2, 'type' => 'theory'],
            ['name' => 'Mathematics II',       'code' => 'MTH201', 'semester' => 2, 'type' => 'theory'],
            ['name' => 'Data Structures',      'code' => 'DST201', 'semester' => 2, 'type' => 'both'],
            ['name' => 'Web Technology I',     'code' => 'WBT201', 'semester' => 2, 'type' => 'both'],
            // Semester 3
            ['name' => 'Database Management',  'code' => 'DBM301', 'semester' => 3, 'type' => 'both'],
            ['name' => 'OOP with Java',        'code' => 'OPJ301', 'semester' => 3, 'type' => 'both'],
            ['name' => 'Computer Networks',    'code' => 'CNT301', 'semester' => 3, 'type' => 'theory'],
            ['name' => 'Web Technology II',    'code' => 'WBT301', 'semester' => 3, 'type' => 'both'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::firstOrCreate(
                ['code' => $subjectData['code']],
                [
                    'program_id' => $program->id,
                    'semester'   => $subjectData['semester'],
                    'name'       => $subjectData['name'],
                    'code'       => $subjectData['code'],
                    'type'       => $subjectData['type'],
                    'full_marks_internal_theory'   => 20,
                    'full_marks_external_theory'   => 80,
                    'pass_marks_internal_theory'   => 8,
                    'pass_marks_external_theory'   => 32,
                    'full_marks_internal_practical'  => 30,
                    'full_marks_external_practical'  => 20,
                    'pass_marks_internal_practical'  => 15,
                    'pass_marks_external_practical'  => 10,
                    'credit_hours' => 3,
                    'is_active'  => true,
                ]
            );
        }

        $this->command->info('Subjects seeded successfully.');
    }
}
