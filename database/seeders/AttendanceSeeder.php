<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * Seeds: 2 weeks of attendance sessions + records for all students.
 */
class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::where('is_active', true)->first();
        if (!$session) {
            $this->command->warn('No active academic session — skipping AttendanceSeeder.');
            return;
        }

        $programs = Program::whereIn('code', ['DIT', 'DCE', 'DEEE', 'DME'])->get();

        foreach ($programs as $program) {
            $department = Department::find($program->department_id);
            $teacher    = Teacher::where('department_id', $department?->id)->first();
            $semester   = 1;
            $subjects   = Subject::where('program_id', $program->id)
                                  ->where('semester', $semester)
                                  ->whereIn('type', ['theory', 'both'])
                                  ->take(2) // 2 subjects per day to keep seed size manageable
                                  ->get();
            $students   = Student::where('program_id', $program->id)
                                  ->where('current_semester', $semester)
                                  ->where('status', 'active')
                                  ->get();

            if (!$teacher || $subjects->isEmpty() || $students->isEmpty()) continue;

            // 10 working days in the past
            $workingDays = [];
            $date = now()->subDays(14);
            while (count($workingDays) < 10) {
                // Skip Saturday (6) — Nepal has Sun-Fri work week
                if ($date->dayOfWeek !== 6) {
                    $workingDays[] = $date->format('Y-m-d');
                }
                $date->addDay();
            }

            foreach ($workingDays as $day) {
                foreach ($subjects as $subject) {
                    $attSession = AttendanceSession::firstOrCreate(
                        [
                            'academic_session_id' => $session->id,
                            'teacher_id'          => $teacher->id,
                            'subject_id'          => $subject->id,
                            'program_id'          => $program->id,
                            'semester'            => $semester,
                            'date'                => $day,
                            'section'             => 'A',
                        ],
                        ['period' => '1st Period']
                    );

                    foreach ($students as $student) {
                        // 85% attendance rate realistically
                        $status = (rand(1, 100) <= 85) ? 'present' : 'absent';

                        Attendance::firstOrCreate(
                            [
                                'attendance_session_id' => $attSession->id,
                                'student_id'            => $student->id,
                            ],
                            ['status' => $status]
                        );
                    }
                }
            }
        }

        $this->command->info('Attendance seeded successfully (10 days × programs × subjects).');
    }
}
