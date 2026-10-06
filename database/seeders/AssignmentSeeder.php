<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $program  = Program::where('code', 'B.Tech')->first();
        $teacher  = Teacher::first();
        $subjects = Subject::where('program_id', $program?->id)->where('semester', 1)->take(3)->get();
        $students = Student::all();

        if (!$program || !$teacher || $subjects->isEmpty()) {
            $this->command->warn('Missing program/teacher/subjects. Run earlier seeders first.');
            return;
        }

        $assignments = [
            [
                'title'       => 'Introduction to Programming',
                'description' => 'Write a C program to demonstrate basic operations like add, subtract, multiply and divide.',
                'due_date'    => now()->addDays(7),
            ],
            [
                'title'       => 'Data Types and Variables',
                'description' => 'Prepare a report explaining different data types in C with examples.',
                'due_date'    => now()->addDays(10),
            ],
            [
                'title'       => 'Loop Structures',
                'description' => 'Implement programs using for, while, and do-while loops to print patterns.',
                'due_date'    => now()->addDays(14),
            ],
        ];

        foreach ($subjects as $index => $subject) {
            $assignmentData = $assignments[$index] ?? $assignments[0];

            $assignment = Assignment::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'title'      => $assignmentData['title'],
                ],
                [
                    'program_id'  => $program->id,
                    'semester'    => 1,
                    'section'     => 'A',
                    'description' => $assignmentData['description'],
                    'due_date'    => $assignmentData['due_date'],
                ]
            );

            // Create submissions for each student
            foreach ($students as $student) {
                AssignmentSubmission::firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id'    => $student->id,
                    ],
                    [
                        'student_note'     => 'Completed as per the requirements.',
                        'status'           => 'graded',
                        'marks_obtained'   => rand(15, 25),
                        'teacher_feedback' => 'Good work. Keep it up.',
                    ]
                );
            }
        }

        $this->command->info('Assignments and submissions seeded successfully.');
    }
}
