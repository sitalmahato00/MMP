<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamSubjectMarkingScheme;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AcademicRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting production-safe Academic Records Seeding...');

        // 1. Resolve Academic Session (use active or latest existing, or fallback)
        $session = AcademicSession::where('is_active', true)->first()
            ?? AcademicSession::latest()->first()
            ?? AcademicSession::firstOrCreate(
                ['name' => date('Y') . '/' . (date('Y') + 1)],
                [
                    'start_date' => date('Y-01-01'),
                    'end_date' => date('Y-12-31'),
                    'is_active' => true,
                    'status' => 'active',
                ]
            );

        // 2. Resolve Department & Program
        $department = Department::where('code', 'IT')->first()
            ?? Department::first()
            ?? Department::firstOrCreate(
                ['code' => 'IT'],
                [
                    'name' => 'Information Technology',
                    'slug' => 'information-technology',
                    'description' => 'Department of Information Technology',
                    'is_active' => true,
                ]
            );

        $program = Program::where('department_id', $department->id)->first()
            ?? Program::first()
            ?? Program::firstOrCreate(
                ['code' => 'B.Tech'],
                [
                    'name' => 'Bachelor of Technology',
                    'slug' => 'bachelor-of-technology',
                    'department_id' => $department->id,
                    'total_semesters' => 8,
                    'duration_years' => 4,
                    'is_active' => true,
                ]
            );

        // 3. Resolve or Create Teacher
        $teacher = Teacher::where('is_active', true)->first() ?? Teacher::first();
        if (!$teacher) {
            $teacherUser = User::withTrashed()->where('email', 'teacher@mmp.edu.np')->first();
            if (!$teacherUser) {
                $teacherUser = User::create([
                    'name' => 'IT Lecturer',
                    'email' => 'teacher@mmp.edu.np',
                    'phone' => '9841000000',
                    'gender' => 'male',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }
            $teacher = Teacher::withTrashed()->firstOrCreate(
                ['user_id' => $teacherUser->id],
                [
                    'department_id' => $department->id,
                    'employee_id' => 'EMP-TEST-' . $teacherUser->id,
                    'designation' => 'Lecturer',
                    'is_active' => true,
                ]
            );
        }

        // 4. Resolve or Create Subjects
        $subjects = Subject::where('program_id', $program->id)->get();
        if ($subjects->isEmpty()) {
            $defaultSubjects = [
                ['name' => 'Programming in C', 'code' => 'IT101', 'semester' => 1],
                ['name' => 'Database Management Systems', 'code' => 'IT102', 'semester' => 1],
                ['name' => 'Web Technology', 'code' => 'IT103', 'semester' => 1],
            ];

            foreach ($defaultSubjects as $sub) {
                Subject::firstOrCreate(
                    ['code' => $sub['code']],
                    [
                        'program_id' => $program->id,
                        'semester' => $sub['semester'],
                        'name' => $sub['name'],
                        'type' => 'both',
                        'full_marks_internal_theory' => 20,
                        'full_marks_external_theory' => 80,
                        'pass_marks_internal_theory' => 8,
                        'pass_marks_external_theory' => 32,
                        'full_marks_internal_practical' => 30,
                        'full_marks_external_practical' => 20,
                        'pass_marks_internal_practical' => 15,
                        'pass_marks_external_practical' => 10,
                        'credit_hours' => 3,
                        'is_active' => true,
                    ]
                );
            }
            $subjects = Subject::where('program_id', $program->id)->take(3)->get();
        }

        // Link Teacher to Subjects in pivot table (subject_teacher)
        foreach ($subjects->take(3) as $subject) {
            DB::table('subject_teacher')->updateOrInsert(
                [
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'academic_session_id' => $session->id,
                    'section' => 'A',
                ],
                [
                    'role' => 'both',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // 5. Resolve or Create Active Students
        $students = Student::where('status', 'active')->get();
        if ($students->isEmpty()) {
            $students = Student::all();
        }
        if ($students->isEmpty()) {
            $studentUser = User::withTrashed()->where('email', 'student@mmp.edu.np')->first();
            if (!$studentUser) {
                $studentUser = User::create([
                    'name' => 'College Student',
                    'email' => 'student@mmp.edu.np',
                    'phone' => '9841000000',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }
            $student = Student::withTrashed()->firstOrCreate(
                ['user_id' => $studentUser->id],
                [
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'academic_session_id' => $session->id,
                    'student_no' => 'DEMO-STU-' . $studentUser->id,
                    'registration_number' => 'DEMO-REG-' . $studentUser->id,
                    'current_semester' => 1,
                    'semester' => 1,
                    'batch' => (string) date('Y'),
                    'section' => 'A',
                    'status' => 'active',
                    'is_active' => true,
                ]
            );
            $students = collect([$student]);
        }

        $activeSubjectList = $subjects->take(3);

        // ─────────────────────────────────────────────────────────────
        // 6. ATTENDANCE (Sessions + Daily Attendance)
        // ─────────────────────────────────────────────────────────────
        $attendanceCount = 0;
        foreach ($activeSubjectList as $subject) {
            for ($daysAgo = 5; $daysAgo >= 1; $daysAgo--) {
                $date = now()->subDays($daysAgo)->toDateString();

                $attSession = AttendanceSession::firstOrCreate(
                    [
                        'academic_session_id' => $session->id,
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'date' => $date,
                    ],
                    [
                        'program_id' => $subject->program_id ?? $program->id,
                        'semester' => $subject->semester ?? 1,
                        'section' => 'A',
                        'period' => '1st',
                    ]
                );

                foreach ($students as $student) {
                    $attendance = Attendance::firstOrCreate(
                        [
                            'attendance_session_id' => $attSession->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'status' => (rand(1, 10) <= 8 ? 'present' : 'absent'),
                            'remarks' => null,
                        ]
                    );
                    if ($attendance->wasRecentlyCreated) {
                        $attendanceCount++;
                    }
                }
            }
        }
        $this->command->info("✓ Attendance: processed sessions for past 5 days ($attendanceCount new records).");

        // ─────────────────────────────────────────────────────────────
        // 7. EXAMS & MARKS
        // ─────────────────────────────────────────────────────────────
        $exam = Exam::firstOrCreate(
            [
                'academic_session_id' => $session->id,
                'name' => 'First Terminal Assessment',
            ],
            [
                'department_id' => $department->id,
                'type' => 'assessment',
                'category' => 'monthly_assessment',
                'assessment_number' => 1,
                'assessment_full_marks' => 100,
                'assessment_pass_marks' => 40,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->subDays(5)->toDateString(),
                'status' => 'completed',
                'marks_open' => false,
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ]
        );

        $exam->programs()->syncWithoutDetaching([
            $program->id => ['semester' => 1],
        ]);

        $marksCount = 0;
        foreach ($activeSubjectList as $subject) {
            // Marking scheme
            ExamSubjectMarkingScheme::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'subject_id' => $subject->id,
                ],
                [
                    'full_marks_internal_theory' => 20,
                    'pass_marks_internal_theory' => 8,
                    'full_marks_external_theory' => 80,
                    'pass_marks_external_theory' => 32,
                    'full_marks_internal_practical' => 30,
                    'pass_marks_internal_practical' => 15,
                    'full_marks_external_practical' => 20,
                    'pass_marks_external_practical' => 10,
                ]
            );

            // Marks per student
            foreach ($students as $student) {
                $mark = Mark::firstOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'program_id' => $subject->program_id ?? $program->id,
                        'teacher_id' => $teacher->id,
                        'semester' => $subject->semester ?? 1,
                        'internal_theory_marks' => 16.00,
                        'external_theory_marks' => 62.50,
                        'internal_practical_marks' => 24.00,
                        'external_practical_marks' => 17.50,
                        'marks_obtained' => 120.00,
                        'total_marks' => 150.00,
                        'pass_marks' => 65.00,
                        'was_present_on_exam_date' => true,
                        'is_absent' => false,
                        'status' => 'published',
                        'remarks' => 'Good performance in assessments.',
                    ]
                );
                if ($mark->wasRecentlyCreated) {
                    $marksCount++;
                }
            }
        }
        $this->command->info("✓ Marks: processed for exam '{$exam->name}' ($marksCount new mark records).");

        // ─────────────────────────────────────────────────────────────
        // 8. ASSIGNMENTS & SUBMISSIONS
        // ─────────────────────────────────────────────────────────────
        $submissionsCount = 0;
        foreach ($activeSubjectList as $subject) {
            $assignment = Assignment::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'title' => 'Core Fundamentals Assignment - ' . $subject->name,
                ],
                [
                    'program_id' => $subject->program_id ?? $program->id,
                    'semester' => $subject->semester ?? 1,
                    'section' => 'A',
                    'description' => 'Complete chapter review questions and submit source code / reports.',
                    'due_date' => now()->addDays(7)->toDateString(),
                ]
            );

            foreach ($students as $student) {
                $sub = AssignmentSubmission::firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'student_note' => 'Assignment completed and uploaded.',
                        'status' => 'graded',
                        'marks_obtained' => 88.00,
                        'teacher_feedback' => 'Well structured, clear logic and correct formatting.',
                    ]
                );
                if ($sub->wasRecentlyCreated) {
                    $submissionsCount++;
                }
            }
        }
        $this->command->info("✓ Assignments: created assignments & graded submissions ($submissionsCount new submissions).");

        $this->command->info('');
        $this->command->info('=========================================================');
        $this->command->info(' Academic Records (Attendance, Marks, Assignments) Ready! ');
        $this->command->info('=========================================================');
    }
}
