<?php

namespace Database\Seeders;

use App\Models\AttendanceSession;
use App\Models\Attendance;
use App\Models\AcademicSession;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $session  = AcademicSession::where('is_active', true)->first();
        $program  = Program::where('code', 'B.Tech')->first();
        $teacher  = Teacher::first();
        $subjects = Subject::where('program_id', $program?->id)->where('semester', 1)->take(3)->get();
        $students = Student::all();

        if (!$session || !$program || !$teacher || $subjects->isEmpty()) {
            $this->command->warn('Missing prerequisites. Run earlier seeders first.');
            return;
        }

        // Create attendance sessions for past 5 days per subject
        foreach ($subjects as $subject) {
            for ($i = 5; $i >= 1; $i--) {
                $date = now()->subDays($i)->toDateString();

                $attSession = AttendanceSession::firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'date'       => $date,
                    ],
                    [
                        'academic_session_id' => $session->id,
                        'program_id'          => $program->id,
                        'semester'            => 1,
                        'section'             => 'A',
                        'period'              => '1st',
                    ]
                );

                // Mark attendance for each student
                foreach ($students as $student) {
                    Attendance::firstOrCreate(
                        [
                            'attendance_session_id' => $attSession->id,
                            'student_id'            => $student->id,
                        ],
                        [
                            // 80% present, 20% absent
                            'status'  => rand(1, 10) <= 8 ? 'present' : 'absent',
                            'remarks' => null,
                        ]
                    );
                }
            }
        }

        $this->command->info('Attendance seeded successfully.');
    }
}
