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
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeds data for the actual logged-in student:
 *   user_id = 80 (sital kumar mahato), student_id = 16
 *   program_id = 2 (DIT), department_id = 2 (IT)
 *   session_id = 1 (2081-2082), current_semester = 1
 */
class FixStudentDataSeeder extends Seeder
{
    private int $studentId   = 16;
    private int $programId   = 2;
    private int $deptId      = 2;
    private int $sessionId   = 1;
    private int $semester    = 1;
    private string $section  = 'A';

    public function run(): void
    {
        $this->command->info('Seeding data for student id=' . $this->studentId);

        $student = Student::findOrFail($this->studentId);
        $session = AcademicSession::findOrFail($this->sessionId);

        // Ensure student is active and semester = 1
        $student->update(['status' => 'active', 'current_semester' => $this->semester, 'section' => $this->section]);

        $subjects  = $this->seedSubjects();
        $teacher   = $this->ensureTeacher();
        $this->seedAttendance($student, $subjects, $teacher, $session);
        $this->seedExamsAndMarks($student, $subjects, $teacher, $session);
        $this->seedAssignments($student, $subjects, $teacher);
        $this->seedNotices($session);
        $this->seedDownloads($subjects);
        $this->seedTimetable($subjects, $teacher, $session);
        $this->clearStudentCache($student);

        $this->command->info('Done. Login at http://127.0.0.1:8000/student/dashboard');
    }

    // ── Subjects ───────────────────────────────────────────

    private function seedSubjects(): \Illuminate\Support\Collection
    {
        $defs = [
            ['code' => 'DIT101', 'name' => 'Engineering Mathematics I',     'type' => 'theory',  'ch' => 4, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 0,  'ep' => 0,  'ipp' => 0,  'epp' => 0 ],
            ['code' => 'DIT102', 'name' => 'Computer Fundamentals',          'type' => 'both',    'ch' => 3, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12, 'ep' => 38, 'ipp' => 6,  'epp' => 19],
            ['code' => 'DIT103', 'name' => 'C Programming',                  'type' => 'both',    'ch' => 3, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12, 'ep' => 38, 'ipp' => 6,  'epp' => 19],
            ['code' => 'DIT104', 'name' => 'Basic Electrical Engineering',   'type' => 'both',    'ch' => 3, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12, 'ep' => 38, 'ipp' => 6,  'epp' => 19],
            ['code' => 'DIT105', 'name' => 'Engineering Physics',            'type' => 'both',    'ch' => 4, 'it' => 20, 'et' => 80, 'ipt' => 8, 'ept' => 32, 'ip' => 12, 'ep' => 38, 'ipp' => 6,  'epp' => 19],
        ];

        $subjects = collect();
        foreach ($defs as $def) {
            $subject = Subject::firstOrCreate(
                ['code' => $def['code'], 'program_id' => $this->programId],
                [
                    'name'                           => $def['name'],
                    'semester'                       => $this->semester,
                    'type'                           => $def['type'],
                    'credit_hours'                   => $def['ch'],
                    'full_marks_internal_theory'     => $def['it'],
                    'full_marks_external_theory'     => $def['et'],
                    'pass_marks_internal_theory'     => $def['ipt'],
                    'pass_marks_external_theory'     => $def['ept'],
                    'full_marks_internal_practical'  => $def['ip'],
                    'full_marks_external_practical'  => $def['ep'],
                    'pass_marks_internal_practical'  => $def['ipp'],
                    'pass_marks_external_practical'  => $def['epp'],
                    'is_active'                      => true,
                ]
            );
            $subjects->push($subject);
        }

        $this->command->info('Subjects: ' . $subjects->count());
        return $subjects;
    }

    // ── Teacher ────────────────────────────────────────────

    private function ensureTeacher(): Teacher
    {
        $user = User::firstOrCreate(
            ['email' => 'dit.teacher@test.com'],
            ['name' => 'Ram Prasad Sharma', 'password' => bcrypt('password'), 'email_verified_at' => now(), 'is_active' => true]
        );
        if (!$user->hasRole('teacher')) $user->assignRole('teacher');

        return Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['department_id' => $this->deptId, 'employee_id' => 'DIT-T01', 'designation' => 'Lecturer', 'qualification' => 'B.Tech', 'join_date' => now()->subYears(2), 'is_active' => true]
        );
    }

    // ── Attendance ─────────────────────────────────────────

    private function seedAttendance(Student $student, \Illuminate\Support\Collection $subjects, Teacher $teacher, AcademicSession $session): void
    {
        // Delete old attendance for this student so we start fresh
        Attendance::where('student_id', $student->id)->delete();
        AttendanceSession::where('academic_session_id', $this->sessionId)
            ->where('program_id', $this->programId)
            ->delete();

        $statuses = ['present','present','present','present','present','present','absent','present','present','late'];
        $count = 0;

        for ($i = 0; $i < 25; $i++) {
            $date    = Carbon::now()->subDays(rand(1, 70));
            $subject = $subjects->random();

            $attSession = AttendanceSession::create([
                'academic_session_id' => $session->id,
                'teacher_id'          => $teacher->id,
                'subject_id'          => $subject->id,
                'program_id'          => $this->programId,
                'semester'            => $this->semester,
                'section'             => $this->section,
                'date'                => $date->toDateString(),
                'period'              => rand(1, 6),
            ]);

            Attendance::create([
                'attendance_session_id' => $attSession->id,
                'student_id'            => $student->id,
                'status'                => $statuses[$i % count($statuses)],
            ]);
            $count++;
        }

        $this->command->info("Attendance records: $count (≈80% present)");
    }

    // ── Exams & Marks ──────────────────────────────────────

    private function seedExamsAndMarks(Student $student, \Illuminate\Support\Collection $subjects, Teacher $teacher, AcademicSession $session): void
    {
        // Remove old marks for this student
        Mark::where('student_id', $student->id)->delete();

        $exam1 = Exam::firstOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'Monthly Assessment 1 (DIT)', 'department_id' => $this->deptId],
            [
                'type' => 'assessment', 'category' => 'monthly_assessment',
                'assessment_number' => 1, 'assessment_full_marks' => 25, 'assessment_pass_marks' => 10,
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date'   => now()->subMonths(2)->addDays(3)->toDateString(),
                'status' => 'results_published', 'marks_open' => true,
                'is_published' => true, 'published_at' => now()->subMonths(2)->addDays(5),
            ]
        );

        $exam2 = Exam::firstOrCreate(
            ['academic_session_id' => $session->id, 'name' => 'Monthly Assessment 2 (DIT)', 'department_id' => $this->deptId],
            [
                'type' => 'assessment', 'category' => 'monthly_assessment',
                'assessment_number' => 2, 'assessment_full_marks' => 25, 'assessment_pass_marks' => 10,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date'   => now()->subMonth()->addDays(3)->toDateString(),
                'status' => 'results_published', 'marks_open' => true,
                'is_published' => true, 'published_at' => now()->subMonth()->addDays(5),
            ]
        );

        $marks1 = [20, 18, 22, 19, 17];
        $marks2 = [22, 21, 20, 23, 19];

        foreach ($subjects as $idx => $subject) {
            foreach ([[$exam1, $marks1], [$exam2, $marks2]] as [$exam, $markSet]) {
                Mark::create([
                    'exam_id'                  => $exam->id,
                    'student_id'               => $student->id,
                    'subject_id'               => $subject->id,
                    'program_id'               => $this->programId,
                    'teacher_id'               => $teacher->id,
                    'semester'                 => $this->semester,
                    'assessment_full_marks'    => 25,
                    'assessment_pass_marks'    => 10,
                    'assessment_obtained_marks'=> $markSet[$idx] ?? 18,
                    'is_absent'                => false,
                    'is_withheld'              => false,
                    'is_delayed'               => false,
                    'status'                   => 'published',
                ]);
            }
        }

        $this->command->info('Exams & marks seeded (2 assessments × ' . $subjects->count() . ' subjects)');
    }


    // ── Assignments ────────────────────────────────────────

    private function seedAssignments(Student $student, \Illuminate\Support\Collection $subjects, Teacher $teacher): void
    {
        $defs = [
            ['title' => 'Lab Report: Basic Circuits',         'days' => 14, 'status' => 'submitted', 'marks' => 17],
            ['title' => 'C Programming: Fibonacci Series',    'days' => 7,  'status' => 'graded',    'marks' => 21],
            ['title' => 'Math I: Differentiation Set',        'days' => 3,  'status' => null,        'marks' => null],
            ['title' => 'Physics: Wave Motion Report',        'days' => -2, 'status' => null,        'marks' => null],
            ['title' => 'Computer Fundamentals: Number Systems','days' => 10,'status' => 'submitted','marks' => 18],
        ];

        foreach ($defs as $i => $def) {
            $subject = $subjects->get($i % $subjects->count());
            $assignment = Assignment::firstOrCreate(
                ['title' => $def['title'], 'teacher_id' => $teacher->id, 'program_id' => $this->programId],
                [
                    'subject_id'  => $subject->id,
                    'semester'    => $this->semester,
                    'section'     => $this->section,
                    'description' => 'Complete and submit as instructed.',
                    'due_date'    => now()->addDays($def['days'])->toDateString(),
                ]
            );

            if ($def['status']) {
                AssignmentSubmission::firstOrCreate(
                    ['assignment_id' => $assignment->id, 'student_id' => $student->id],
                    [
                        'student_note'    => 'Submitted.',
                        'status'          => $def['status'],
                        'marks_obtained'  => $def['marks'],
                        'teacher_feedback'=> $def['status'] === 'graded' ? 'Good work!' : null,
                    ]
                );
            }
        }

        $this->command->info('Assignments seeded: ' . count($defs));
    }

    // ── Notices ────────────────────────────────────────────

    private function seedNotices(AcademicSession $session): void
    {
        $author = User::where('email', 'sitalmahato077@gmail.com')->first()
                ?? User::first();

        $notices = [
            ['title' => 'First Semester Exam Schedule',      'type' => 'exam',       'days' => -2,  'content' => 'The first semester final exam will be held from Shrawan 15-22, 2082. Carry your admit card.'],
            ['title' => 'College Reopening After Dashain',   'type' => 'general',    'days' => -10, 'content' => 'Classes resume from Kartik 1, 2081 after Dashain and Tihar holidays.'],
            ['title' => 'Library Timing Update',             'type' => 'general',    'days' => -5,  'content' => 'Library open 7 AM - 6 PM on all working days. Book issue limit: 7 days.'],
            ['title' => 'Annual Sports Day',                 'type' => 'event',      'days' => -1,  'content' => 'Sports Day on Falgun 10, 2081. Register from Falgun 1 at the office.'],
            ['title' => 'Scholarship Applications Open',     'type' => 'general',    'days' => -3,  'content' => 'CTEVT merit scholarship applications open. Eligible students apply at admin office by Magh 1, 2081.'],
            ['title' => 'IT Lab Maintenance Notice',         'type' => 'department', 'days' => -1,  'content' => 'IT Lab closed for maintenance Poush 18-20, 2081. Practical classes will be rescheduled.'],
        ];

        foreach ($notices as $n) {
            $slug = \Illuminate\Support\Str::slug($n['title']) . '-' . rand(1000, 9999);
            Notice::firstOrCreate(
                ['title' => $n['title']],
                [
                    'slug'          => $slug,
                    'content'       => $n['content'],
                    'type'          => $n['type'],
                    'department_id' => $n['type'] === 'department' ? $this->deptId : null,
                    'program_id'    => null,
                    'semester'      => null,
                    'created_by'    => $author->id,
                    'is_published'  => true,
                    'published_at'  => now()->addDays($n['days']),
                ]
            );
        }

        $this->command->info('Notices seeded: ' . count($notices));
    }

    // ── Downloads ──────────────────────────────────────────

    private function seedDownloads(\Illuminate\Support\Collection $subjects): void
    {
        $uploader = User::where('email', 'dit.teacher@test.com')->first();
        $subjectsByCode = $subjects->keyBy('code');

        $files = [
            ['title' => 'DIT Semester 1 Syllabus',           'cat' => 'syllabus',   'code' => null],
            ['title' => 'C Programming Lab Manual',          'cat' => 'lab_manual', 'code' => 'DIT103'],
            ['title' => 'Engineering Physics Formula Sheet', 'cat' => 'notes',      'code' => 'DIT105'],
            ['title' => 'Past Question Papers - Math I',     'cat' => 'question',   'code' => 'DIT101'],
            ['title' => 'Computer Fundamentals Notes',       'cat' => 'notes',      'code' => 'DIT102'],
            ['title' => 'Admission & Re-registration Form',  'cat' => 'form',       'code' => null],
            ['title' => 'CTEVT Examination Rules 2081',      'cat' => 'circular',   'code' => null],
        ];

        foreach ($files as $f) {
            $subjectId = $f['code'] && $subjectsByCode->has($f['code'])
                ? $subjectsByCode->get($f['code'])->id
                : null;

            Download::firstOrCreate(
                ['title' => $f['title']],
                [
                    'file_path'    => 'downloads/placeholder.pdf',
                    'file_name'    => \Illuminate\Support\Str::slug($f['title']) . '.pdf',
                    'file_type'    => 'pdf',
                    'file_size'    => rand(50000, 500000),
                    'description'  => $f['title'],
                    'category'     => $f['cat'],
                    'department_id'=> $this->deptId,
                    'subject_id'   => $subjectId,
                    'program_id'   => $this->programId,
                    'semester'     => $subjectId ? $this->semester : null,
                    'is_public'    => true,
                    'visibility'   => 'public',
                    'uploaded_by'  => $uploader?->id,
                ]
            );
        }

        $this->command->info('Downloads seeded: ' . count($files));
    }

    // ── Timetable ──────────────────────────────────────────

    private function seedTimetable(\Illuminate\Support\Collection $subjects, Teacher $teacher, AcademicSession $session): void
    {
        $timetable = Timetable::firstOrCreate(
            ['academic_session_id' => $session->id, 'program_id' => $this->programId, 'semester' => $this->semester, 'section' => $this->section],
            ['start_date' => now()->subMonths(4)->toDateString(), 'effective_from' => now()->subMonths(4)->toDateString(), 'is_active' => true]
        );

        $days    = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $periods = [['start' => '07:00', 'end' => '08:00'], ['start' => '08:00', 'end' => '09:00'], ['start' => '10:00', 'end' => '11:00']];

        foreach ($days as $day) {
            foreach ($periods as $pidx => $period) {
                $subject = $subjects->get($pidx % $subjects->count());
                TimetableSlot::firstOrCreate(
                    ['timetable_id' => $timetable->id, 'day_of_week' => $day, 'start_time' => $period['start']],
                    [
                        'subject_id'  => $subject->id,
                        'teacher_id'  => $teacher->id,
                        'end_time'    => $period['end'],
                        'room_number' => 'IT-' . rand(101, 108),
                        'type'        => $pidx === 2 ? 'lab' : 'theory',
                        'group'       => 'A',
                        'duration'    => 60,
                    ]
                );
            }
        }

        $this->command->info('Timetable seeded: 5 days × 3 periods');
    }

    // ── Cache clear ────────────────────────────────────────

    private function clearStudentCache(Student $student): void
    {
        $keys = [
            "student_dashboard_kpi_{$student->id}_v5",
            "student_dashboard_marks_summary_{$student->id}_v2",
            "student_dashboard_attendance_summary_{$student->id}_v1",
            "student_dashboard_notices_{$student->department_id}_v4",
            "student_upcoming_assignments_{$student->id}_v1",
            "student_chart_{$student->id}_v1",
        ];
        foreach ($keys as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }
        $this->command->info('Cache cleared for student ' . $student->id);
    }
}
