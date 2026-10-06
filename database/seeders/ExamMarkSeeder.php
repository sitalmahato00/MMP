<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * Seeds: exams (1 assessment + 1 final), marks for all students × subjects.
 */
class ExamMarkSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::where('is_active', true)->first();
        if (!$session) {
            $this->command->warn('No active academic session found — skipping ExamMarkSeeder.');
            return;
        }

        $programs = Program::with('subjects')->whereIn('code', ['DIT', 'DCE', 'DEEE', 'DME'])->get();

        foreach ($programs as $program) {
            $department  = Department::find($program->department_id);
            $semester    = 1;
            $subjects    = $program->subjects()->where('semester', $semester)->get();
            $students    = Student::where('program_id', $program->id)
                                  ->where('current_semester', $semester)
                                  ->where('status', 'active')
                                  ->get();

            if ($students->isEmpty() || $subjects->isEmpty()) continue;

            // ── Monthly Assessment 1 ─────────────────────────────────────────
            $assessment = Exam::firstOrCreate(
                [
                    'academic_session_id' => $session->id,
                    'department_id'       => $department->id,
                    'name'                => 'First Monthly Assessment 2081 - ' . $program->code,
                    'type'                => 'assessment',
                ],
                [
                    'category'               => 'monthly_assessment',
                    'assessment_number'      => 1,
                    'assessment_full_marks'  => 25,
                    'assessment_pass_marks'  => 10,
                    'start_date'             => '2024-08-15',
                    'end_date'               => '2024-08-20',
                    'status'                 => 'results_published',
                    'marks_open'             => true,
                    'is_published'           => true,
                    'published_at'           => now()->subDays(20),
                ]
            );

            // Link exam to program+semester
            $assessment->programs()->syncWithoutDetaching([
                $program->id => ['semester' => $semester]
            ]);

            // ── Final / Board Exam ───────────────────────────────────────────
            $finalExam = Exam::firstOrCreate(
                [
                    'academic_session_id' => $session->id,
                    'department_id'       => $department->id,
                    'name'                => 'CTEVT First Semester Examination 2081 - ' . $program->code,
                    'type'                => 'final',
                ],
                [
                    'category'       => 'ctevt_final',
                    'start_date'     => '2024-10-01',
                    'end_date'       => '2024-10-15',
                    'status'         => 'ongoing',
                    'marks_open'     => false,
                    'is_published'   => false,
                ]
            );

            $finalExam->programs()->syncWithoutDetaching([
                $program->id => ['semester' => $semester]
            ]);

            // ── Marks for Assessment ─────────────────────────────────────────
            foreach ($students as $student) {
                $teacher = Teacher::where('department_id', $department->id)->first();

                foreach ($subjects as $subject) {
                    $fullMarks = 25;
                    $obtained  = rand(12, 25); // realistic marks

                    Mark::firstOrCreate(
                        [
                            'exam_id'    => $assessment->id,
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'program_id'                    => $program->id,
                            'teacher_id'                    => $teacher?->id,
                            'semester'                      => $semester,
                            'assessment_full_marks'         => $fullMarks,
                            'assessment_pass_marks'         => 10,
                            'assessment_obtained_marks'     => $obtained,
                            'assessment_attendance_percent' => rand(75, 100),
                            'marks_obtained'                => $obtained,
                            'total_marks'                   => $fullMarks,
                            'pass_marks'                    => 10,
                            'is_absent'                     => false,
                            'is_withheld'                   => false,
                            'status'                        => 'published',
                        ]
                    );
                }
            }
        }

        $this->command->info('Exams and marks seeded successfully.');
    }
}
