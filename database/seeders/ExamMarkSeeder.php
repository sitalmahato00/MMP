<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ExamSubjectMarkingScheme;
use Illuminate\Database\Seeder;

class ExamMarkSeeder extends Seeder
{
    public function run(): void
    {
        $session    = AcademicSession::where('is_active', true)->first();
        $department = Department::where('code', 'IT')->first();
        $program    = Program::where('code', 'B.Tech')->first();
        $teacher    = Teacher::first();

        if (!$session || !$program || !$teacher) {
            $this->command->warn('Missing session/program/teacher. Run earlier seeders first.');
            return;
        }

        // Create an exam
        $exam = Exam::firstOrCreate(
            [
                'academic_session_id' => $session->id,
                'name'                => 'First Semester Assessment',
            ],
            [
                'department_id'     => $department?->id,
                'type'              => 'assessment',
                'category'          => 'monthly_assessment',
                'assessment_number' => 1,
                'assessment_full_marks' => 100,
                'assessment_pass_marks' => 40,
                'start_date'        => now()->subDays(7),
                'end_date'          => now()->subDays(5),
                'status'            => 'completed',
                'marks_open'        => true,
                'is_published'      => true,
                'published_at'      => now()->subDays(3),
            ]
        );

        // Link exam to program/semester
        $exam->programs()->syncWithoutDetaching([
            $program->id => ['semester' => 1],
        ]);

        $subjects = Subject::where('program_id', $program->id)
                           ->where('semester', 1)
                           ->take(4)
                           ->get();

        $students = Student::whereHas('program', fn($q) => $q->where('code', 'B.Tech'))->get();

        foreach ($subjects as $subject) {
            // Create marking scheme
            ExamSubjectMarkingScheme::firstOrCreate(
                ['exam_id' => $exam->id, 'subject_id' => $subject->id],
                [
                    'full_marks_internal_theory'   => 20,
                    'pass_marks_internal_theory'   => 8,
                    'full_marks_external_theory'   => 80,
                    'pass_marks_external_theory'   => 32,
                    'full_marks_internal_practical'  => 0,
                    'pass_marks_internal_practical'  => 0,
                    'full_marks_external_practical'  => 0,
                    'pass_marks_external_practical'  => 0,
                ]
            );

            // Seed marks for each student
            foreach ($students as $student) {
                $internal = rand(14, 20);
                $external = rand(55, 80);

                Mark::firstOrCreate(
                    [
                        'exam_id'    => $exam->id,
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'program_id'              => $program->id,
                        'teacher_id'              => $teacher->id,
                        'semester'                => 1,
                        'internal_theory_marks'   => $internal,
                        'external_theory_marks'   => $external,
                        'marks_obtained'          => $internal + $external,
                        'total_marks'             => 100,
                        'pass_marks'              => 40,
                        'status'                  => 'published',
                    ]
                );
            }
        }

        $this->command->info('Exams and marks seeded successfully.');
    }
}
