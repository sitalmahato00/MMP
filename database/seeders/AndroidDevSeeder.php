<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Department;
use App\Models\Download;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AndroidDevSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Seeding Android Dev data...');

        $session = $this->seedSession();
        $dept = $this->seedDepartment();
        $program = $this->seedProgram($dept);
        $subjects = $this->seedSubjects($program);
        [$hodUser, $teacherUser] = $this->seedStaff($dept);
        $studentUser = $this->seedStudent($dept, $program, $session);
        $this->seedParent($studentUser['student']);
        $attendanceSessions = $this->seedAttendanceSessions($session, $teacherUser['teacher'], $subjects, $program, $studentUser['student']);
        $this->seedExamsAndMarks($session, $dept, $program, $subjects, $studentUser['student'], $teacherUser['teacher']);
        $this->seedAssignments($teacherUser['teacher'], $subjects, $program, $studentUser['student']);
        $this->seedNotices($session, $dept, $program, $hodUser['user']);
        $this->seedDownloads($dept, $subjects, $program, $teacherUser['user']);
        $this->seedTimetable($session, $program, $subjects, $teacherUser['teacher']);

        $this->command->info('');
        $this->command->info('=== Android Dev Seed Complete ===');
        $this->command->info('Student: student@test.com / password');
        $this->command->info('Teacher: teacher@test.com / password');
        $this->command->info('HOD:     hod@test.com / password');
        $this->command->info('Parent:  parent@test.com / password');
        $this->command->info('API URL: http://127.0.0.1:8000/api/v1');
    }

    // ── Session ────────────────────────────────────────────

    private function seedSession(): AcademicSession
    {
        return AcademicSession::updateOrCreate(
            ['name' => '2081-2082'],
            [
                'name_bs' => '2081-2082',
                'start_date' => now()->subMonths(4)->toDateString(),
                'end_date' => now()->addMonths(8)->toDateString(),
                'is_active' => true,
                'status' => 'active',
                'is_locked' => false,
                'activated_at' => now()->subMonths(4),
                'ended_at' => null,
                'notes' => 'Active session for Android development testing.',
            ]
        );
    }

    // ── Department ─────────────────────────────────────────

    private function seedDepartment(): Department
    {
        return Department::firstOrCreate(
            ['code' => 'CE'],
            [
                'name' => 'Computer Engineering',
                'slug' => 'computer-engineering',
                'is_active' => true,
            ]
        );
    }

    // ── Program ────────────────────────────────────────────

    private function seedProgram(Department $dept): Program
    {
        return Program::firstOrCreate(
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
    }


    // ── Subjects ───────────────────────────────────────────

    private function seedSubjects(Program $program): \Illuminate\Support\Collection
    {
        $definitions = [
            ['code' => 'ENG101', 'name' => 'Engineering Mathematics I',   'type' => 'theory',     'credit_hours' => 4, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 0, 'ep' => 0, 'ipp' => 0, 'epp' => 0],
            ['code' => 'ENG102', 'name' => 'Engineering Physics',          'type' => 'both',       'credit_hours' => 4, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12,'ep' => 38,'ipp' => 6, 'epp' => 19],
            ['code' => 'COM101', 'name' => 'Computer Fundamentals',        'type' => 'both',       'credit_hours' => 3, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12,'ep' => 38,'ipp' => 6, 'epp' => 19],
            ['code' => 'PRG101', 'name' => 'C Programming',                'type' => 'both',       'credit_hours' => 3, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12,'ep' => 38,'ipp' => 6, 'epp' => 19],
            ['code' => 'ELC101', 'name' => 'Basic Electrical Engineering', 'type' => 'both',       'credit_hours' => 3, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12,'ep' => 38,'ipp' => 6, 'epp' => 19],
            ['code' => 'ENG201', 'name' => 'Engineering Mathematics II',   'type' => 'theory',     'credit_hours' => 4, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 0, 'ep' => 0, 'ipp' => 0, 'epp' => 0],
        ];

        $subjects = collect();
        foreach ($definitions as $sem => $def) {
            $semester = $sem < 5 ? 1 : 2;
            $subject = Subject::firstOrCreate(
                ['code' => $def['code'], 'program_id' => $program->id],
                [
                    'name' => $def['name'],
                    'semester' => $semester,
                    'type' => $def['type'],
                    'credit_hours' => $def['credit_hours'],
                    'full_marks_internal_theory' => $def['it'],
                    'full_marks_external_theory' => $def['et'],
                    'pass_marks_internal_theory' => $def['ipt'],
                    'pass_marks_external_theory' => $def['ept'],
                    'full_marks_internal_practical' => $def['ip'],
                    'full_marks_external_practical' => $def['ep'],
                    'pass_marks_internal_practical' => $def['ipp'],
                    'pass_marks_external_practical' => $def['epp'],
                    'is_active' => true,
                ]
            );
            $subjects->push($subject);
        }

        $this->command->info('Subjects seeded: ' . $subjects->count());
        return $subjects;
    }


    // ── Staff ──────────────────────────────────────────────

    private function seedStaff(Department $dept): array
    {
        foreach (['hod', 'teacher', 'student', 'parent'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // HOD
        $hodUser = User::firstOrCreate(
            ['email' => 'hod@test.com'],
            ['name' => 'HOD User', 'password' => bcrypt('password'), 'email_verified_at' => now(), 'is_active' => true]
        );
        if (!$hodUser->hasRole('hod')) $hodUser->assignRole('hod');
        $hodTeacher = Teacher::firstOrCreate(
            ['user_id' => $hodUser->id],
            ['department_id' => $dept->id, 'employee_id' => 'T001', 'designation' => 'HOD', 'qualification' => 'M.Tech', 'join_date' => now()->subYears(5), 'is_active' => true]
        );
        $dept->update(['hod_id' => $hodUser->id]);

        // Teacher
        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@test.com'],
            ['name' => 'Ram Prasad Sharma', 'password' => bcrypt('password'), 'email_verified_at' => now(), 'is_active' => true]
        );
        if (!$teacherUser->hasRole('teacher')) $teacherUser->assignRole('teacher');
        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['department_id' => $dept->id, 'employee_id' => 'T002', 'designation' => 'Lecturer', 'qualification' => 'B.Tech', 'join_date' => now()->subYears(2), 'is_active' => true]
        );

        $this->command->info('Staff seeded.');
        return [
            ['user' => $hodUser, 'teacher' => $hodTeacher],
            ['user' => $teacherUser, 'teacher' => $teacher],
        ];
    }


    // ── Student ────────────────────────────────────────────

    private function seedStudent(Department $dept, Program $program, AcademicSession $session): array
    {
        $studentUser = User::firstOrCreate(
            ['email' => 'student@test.com'],
            [
                'name' => 'Ramesh Kumar Thapa',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        if (!$studentUser->hasRole('student')) $studentUser->assignRole('student');

        $student = Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'department_id' => $dept->id,
                'program_id' => $program->id,
                'academic_session_id' => $session->id,
                'student_no' => 'S001',
                'registration_number' => 'REG-2081-001',
                'batch' => '2081',
                'current_semester' => 1,
                'section' => 'A',
                'status' => 'active',
                'admission_date' => now()->subMonths(4)->toDateString(),
            ]
        );

        $this->command->info('Student seeded: student@test.com');
        return ['user' => $studentUser, 'student' => $student];
    }

    // ── Parent ─────────────────────────────────────────────

    private function seedParent(Student $student): void
    {
        $parentUser = User::firstOrCreate(
            ['email' => 'parent@test.com'],
            ['name' => 'Binod Kumar Thapa', 'password' => bcrypt('password'), 'email_verified_at' => now(), 'is_active' => true]
        );
        if (!$parentUser->hasRole('parent')) $parentUser->assignRole('parent');

        $parent = ParentModel::firstOrCreate(
            ['user_id' => $parentUser->id],
            ['relation_to_student' => 'father', 'occupation' => 'Business']
        );

        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            $parent->students()->attach($student->id);
        }

        $this->command->info('Parent seeded: parent@test.com');
    }


    // ── Attendance ─────────────────────────────────────────

    private function seedAttendanceSessions(
        AcademicSession $session, Teacher $teacher,
        \Illuminate\Support\Collection $subjects, Program $program, Student $student
    ): \Illuminate\Support\Collection {
        $sessions = collect();
        $sem1Subjects = $subjects->where('semester', 1)->values();
        $statuses = ['present', 'present', 'present', 'present', 'present', 'absent', 'late', 'present', 'present', 'present'];

        // Create 20 attendance sessions spread over last 60 days
        for ($i = 0; $i < 20; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $subject = $sem1Subjects->random();

            $attSession = AttendanceSession::firstOrCreate(
                [
                    'academic_session_id' => $session->id,
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'date' => $date->toDateString(),
                    'period' => rand(1, 6),
                ],
                [
                    'program_id' => $program->id,
                    'semester' => 1,
                    'section' => 'A',
                ]
            );

            $statusIndex = $i % count($statuses);
            Attendance::firstOrCreate(
                ['attendance_session_id' => $attSession->id, 'student_id' => $student->id],
                ['status' => $statuses[$statusIndex], 'remarks' => null]
            );

            $sessions->push($attSession);
        }

        $this->command->info('Attendance sessions seeded: 20');
        return $sessions;
    }


    // ── Exams & Marks ──────────────────────────────────────

    private function seedExamsAndMarks(
        AcademicSession $session, Department $dept, Program $program,
        \Illuminate\Support\Collection $subjects, Student $student, Teacher $teacher
    ): void {
        $sem1Subjects = $subjects->where('semester', 1)->values();

        // Monthly Assessment 1
        $exam1 = Exam::firstOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'Monthly Assessment 1', 'department_id' => $dept->id],
            [
                'type' => 'assessment',
                'category' => 'monthly_assessment',
                'assessment_number' => 1,
                'assessment_full_marks' => 25,
                'assessment_pass_marks' => 10,
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->subMonths(2)->addDays(3)->toDateString(),
                'status' => 'results_published',
                'marks_open' => true,
                'is_published' => true,
                'published_at' => now()->subMonths(2)->addDays(5),
            ]
        );

        // Monthly Assessment 2
        $exam2 = Exam::firstOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'Monthly Assessment 2', 'department_id' => $dept->id],
            [
                'type' => 'assessment',
                'category' => 'monthly_assessment',
                'assessment_number' => 2,
                'assessment_full_marks' => 25,
                'assessment_pass_marks' => 10,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->subMonth()->addDays(3)->toDateString(),
                'status' => 'results_published',
                'marks_open' => true,
                'is_published' => true,
                'published_at' => now()->subMonth()->addDays(5),
            ]
        );

        // CTEVT Final Exam (upcoming)
        $exam3 = Exam::firstOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'First Semester Final', 'department_id' => $dept->id],
            [
                'type' => 'final',
                'category' => 'ctevt_final',
                'start_date' => now()->addMonths(2)->toDateString(),
                'end_date' => now()->addMonths(2)->addDays(7)->toDateString(),
                'status' => 'upcoming',
                'marks_open' => false,
                'is_published' => false,
            ]
        );

        // Seed marks for assessment exams
        $marksData1 = [18, 20, 15, 22, 17];
        $marksData2 = [21, 19, 23, 20, 16];

        foreach ($sem1Subjects->take(5) as $idx => $subject) {
            Mark::firstOrCreate(
                ['exam_id' => $exam1->id, 'student_id' => $student->id, 'subject_id' => $subject->id],
                [
                    'program_id' => $program->id,
                    'teacher_id' => $teacher->id,
                    'semester' => 1,
                    'assessment_full_marks' => 25,
                    'assessment_pass_marks' => 10,
                    'assessment_obtained_marks' => $marksData1[$idx] ?? 15,
                    'is_absent' => false,
                    'is_withheld' => false,
                    'is_delayed' => false,
                    'status' => 'published',
                ]
            );

            Mark::firstOrCreate(
                ['exam_id' => $exam2->id, 'student_id' => $student->id, 'subject_id' => $subject->id],
                [
                    'program_id' => $program->id,
                    'teacher_id' => $teacher->id,
                    'semester' => 1,
                    'assessment_full_marks' => 25,
                    'assessment_pass_marks' => 10,
                    'assessment_obtained_marks' => $marksData2[$idx] ?? 18,
                    'is_absent' => false,
                    'is_withheld' => false,
                    'is_delayed' => false,
                    'status' => 'published',
                ]
            );
        }

        $this->command->info('Exams and marks seeded: 2 assessments + 1 final exam');
    }


    // ── Assignments ────────────────────────────────────────

    private function seedAssignments(
        Teacher $teacher, \Illuminate\Support\Collection $subjects,
        Program $program, Student $student
    ): void {
        $sem1Subjects = $subjects->where('semester', 1)->values();

        $assignmentDefs = [
            ['title' => 'Lab Report: Basic Circuit Analysis',     'desc' => 'Submit a detailed lab report covering Kirchhoff\'s laws experiments.',              'days' => 10,  'status' => 'submitted', 'marks' => 18],
            ['title' => 'C Programming: Fibonacci Series',        'desc' => 'Write a C program to print Fibonacci series up to N terms using recursion.',          'days' => 5,   'status' => 'graded',    'marks' => 22],
            ['title' => 'Engineering Math: Differentiation Set',  'desc' => 'Solve the given differentiation problems from Chapter 3 of the textbook.',            'days' => -3,  'status' => 'pending',   'marks' => null],
            ['title' => 'Physics: Wave Motion Report',            'desc' => 'Prepare a report on wave motion concepts including frequency, amplitude, and period.', 'days' => -7,  'status' => 'pending',   'marks' => null],
            ['title' => 'Computer Fundamentals: Number Systems',  'desc' => 'Complete the worksheet on binary, octal, decimal, and hexadecimal conversions.',      'days' => 14,  'status' => 'submitted', 'marks' => 19],
        ];

        foreach ($assignmentDefs as $i => $def) {
            $subject = $sem1Subjects->get($i % $sem1Subjects->count());
            $assignment = Assignment::firstOrCreate(
                ['title' => $def['title'], 'teacher_id' => $teacher->id],
                [
                    'subject_id' => $subject->id,
                    'program_id' => $program->id,
                    'semester' => 1,
                    'section' => 'A',
                    'description' => $def['desc'],
                    'due_date' => now()->addDays($def['days'])->toDateString(),
                ]
            );

            if ($def['status'] !== 'pending') {
                AssignmentSubmission::firstOrCreate(
                    ['assignment_id' => $assignment->id, 'student_id' => $student->id],
                    [
                        'student_note' => 'Submitted as required.',
                        'status' => $def['status'],
                        'marks_obtained' => $def['marks'],
                        'teacher_feedback' => $def['status'] === 'graded' ? 'Good work! Keep it up.' : null,
                    ]
                );
            }
        }

        $this->command->info('Assignments seeded: ' . count($assignmentDefs));
    }


    // ── Notices ────────────────────────────────────────────

    private function seedNotices(AcademicSession $session, Department $dept, Program $program, User $author): void
    {
        $notices = [
            ['title' => 'Exam Schedule for First Semester',          'type' => 'exam',       'days' => -2,  'content' => 'The first semester final examination will be conducted from Shrawan 15 to Shrawan 22, 2082. Students are advised to prepare accordingly and carry their admit cards.'],
            ['title' => 'College Reopening After Dashain Holiday',   'type' => 'general',    'days' => -10, 'content' => 'Classes will resume from Kartik 1, 2081 after the Dashain and Tihar holidays. All students are expected to be present on time.'],
            ['title' => 'Library Timing Update',                     'type' => 'general',    'days' => -5,  'content' => 'The college library will now remain open from 7:00 AM to 6:00 PM on all working days. Students can issue books for up to 7 days.'],
            ['title' => 'Sports Day Event Announcement',             'type' => 'event',      'days' => -1,  'content' => 'Annual Sports Day will be held on Falgun 10, 2081. All students are encouraged to participate in various sports events. Registration starts from Falgun 1.'],
            ['title' => 'Assignment Submission Deadline Reminder',   'type' => 'general',    'days' => 0,   'content' => 'Please note that all pending assignments must be submitted before Poush 20, 2081. Late submissions will not be accepted.'],
            ['title' => 'Scholarship Application Open',              'type' => 'general',    'days' => -3,  'content' => 'Applications for the CTEVT merit scholarship are now open. Eligible students (above 75% attendance and 60% marks) may apply at the admin office by Magh 1, 2081.'],
            ['title' => 'Computer Lab Maintenance Notice',           'type' => 'department', 'days' => -1,  'content' => 'The computer lab will be closed for maintenance from Poush 18-20, 2081. Practical classes scheduled during this period will be rescheduled.'],
        ];

        foreach ($notices as $n) {
            $slug = \Illuminate\Support\Str::slug($n['title']) . '-' . rand(1000, 9999);
            Notice::firstOrCreate(
                ['title' => $n['title']],
                [
                    'slug' => $slug,
                    'content' => $n['content'],
                    'type' => $n['type'],
                    'department_id' => in_array($n['type'], ['department', 'exam']) ? $dept->id : null,
                    'program_id' => null,
                    'semester' => null,
                    'created_by' => $author->id,
                    'is_published' => true,
                    'published_at' => now()->addDays($n['days']),
                ]
            );
        }

        $this->command->info('Notices seeded: ' . count($notices));
    }


    // ── Downloads ──────────────────────────────────────────

    private function seedDownloads(Department $dept, \Illuminate\Support\Collection $subjects, Program $program, User $uploader): void
    {
        $files = [
            ['title' => 'Semester 1 Syllabus',                      'desc' => 'Full syllabus for Semester 1 subjects as per CTEVT curriculum.', 'category' => 'syllabus',  'subject' => null],
            ['title' => 'C Programming Lab Manual',                  'desc' => 'Step-by-step lab manual for C Programming practicals.',          'category' => 'lab_manual','subject' => 'PRG101'],
            ['title' => 'Engineering Physics Formula Sheet',         'desc' => 'Quick reference formula sheet for Engineering Physics exam.',     'category' => 'notes',     'subject' => 'ENG102'],
            ['title' => 'Past Question Papers - Math I',             'desc' => 'CTEVT past question papers for Engineering Mathematics I.',       'category' => 'question',  'subject' => 'ENG101'],
            ['title' => 'Computer Fundamentals Notes (Unit 1-3)',    'desc' => 'Summarized notes for Computer Fundamentals first three units.',   'category' => 'notes',     'subject' => 'COM101'],
            ['title' => 'Admission Form Template',                   'desc' => 'Template for fresh admission and re-registration.',               'category' => 'form',      'subject' => null],
            ['title' => 'CTEVT Examination Rules 2081',              'desc' => 'Official CTEVT rules and regulations for examinations.',          'category' => 'circular',  'subject' => null],
        ];

        $sem1Subjects = $subjects->where('semester', 1)->keyBy('code');

        foreach ($files as $f) {
            $subjectId = null;
            if ($f['subject'] && $sem1Subjects->has($f['subject'])) {
                $subjectId = $sem1Subjects->get($f['subject'])->id;
            }

            Download::firstOrCreate(
                ['title' => $f['title']],
                [
                    'file_path' => 'downloads/placeholder.pdf',
                    'file_name' => \Illuminate\Support\Str::slug($f['title']) . '.pdf',
                    'file_type' => 'pdf',
                    'file_size' => rand(50000, 500000),
                    'description' => $f['desc'],
                    'category' => $f['category'],
                    'department_id' => $dept->id,
                    'subject_id' => $subjectId,
                    'program_id' => $program->id,
                    'semester' => $subjectId ? 1 : null,
                    'is_public' => true,
                    'visibility' => 'public',
                    'uploaded_by' => $uploader->id,
                ]
            );
        }

        $this->command->info('Downloads seeded: ' . count($files));
    }


    // ── Timetable ──────────────────────────────────────────

    private function seedTimetable(AcademicSession $session, Program $program, \Illuminate\Support\Collection $subjects, Teacher $teacher): void
    {
        $timetable = Timetable::firstOrCreate(
            ['academic_session_id' => $session->id, 'program_id' => $program->id, 'semester' => 1, 'section' => 'A'],
            [
                'start_date' => now()->subMonths(4)->toDateString(),
                'effective_from' => now()->subMonths(4)->toDateString(),
                'is_active' => true,
            ]
        );

        $sem1Subjects = $subjects->where('semester', 1)->values();

        // Mon-Fri, 3 periods per day
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $periods = [
            ['start' => '07:00', 'end' => '08:00'],
            ['start' => '08:00', 'end' => '09:00'],
            ['start' => '10:00', 'end' => '11:00'],
        ];

        foreach ($days as $day) {
            foreach ($periods as $pidx => $period) {
                $subject = $sem1Subjects->get($pidx % $sem1Subjects->count());
                TimetableSlot::firstOrCreate(
                    ['timetable_id' => $timetable->id, 'day_of_week' => $day, 'start_time' => $period['start']],
                    [
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'end_time' => $period['end'],
                        'room_number' => 'Room ' . rand(101, 110),
                        'type' => $pidx === 2 ? 'lab' : 'theory',
                        'group' => 'A',
                        'duration' => 60,
                    ]
                );
            }
        }

        $this->command->info('Timetable seeded: 5 days × 3 periods');
    }
}
