<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class CollegeAttendanceSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        $session = $this->collegeSession();

        foreach ($this->departmentCatalog() as $departmentIndex => $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();
            $program = Program::query()->where('code', $profile['program_code'])->first();

            if (! $department || ! $program) {
                continue;
            }

            for ($semester = 1; $semester <= self::SEMESTER_COUNT; $semester++) {
                $students = Student::query()
                    ->where('program_id', $program->id)
                    ->where('current_semester', $semester)
                    ->orderBy('id')
                    ->get();

                $subjects = Subject::query()
                    ->where('program_id', $program->id)
                    ->where('semester', $semester)
                    ->orderBy('id')
                    ->get();

                foreach ($subjects as $subjectIndex => $subject) {
                    $teacher = Teacher::query()
                        ->where('department_id', $department->id)
                        ->whereHas('subjects', fn ($query) => $query->where('subjects.id', $subject->id))
                        ->first();

                    if (! $teacher) {
                        continue;
                    }

                    $attendanceSession = AttendanceSession::query()->updateOrCreate(
                        [
                            'academic_session_id' => $session->id,
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subject->id,
                            'program_id' => $program->id,
                            'semester' => $semester,
                            'section' => 'A',
                            'date' => now()->subDays(($semester * 7) + $subjectIndex + 1)->toDateString(),
                        ],
                        [
                            'period' => sprintf('Period %d', $subjectIndex + 1),
                        ]
                    );

                    $rows = [];
                    foreach ($students as $studentIndex => $student) {
                        $status = $this->attendanceStatus($studentIndex, $subjectIndex, $semester, $departmentIndex + 1);
                        $rows[] = [
                            'attendance_session_id' => $attendanceSession->id,
                            'student_id' => $student->id,
                            'status' => $status,
                            'remarks' => $this->attendanceRemark($status),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    foreach (array_chunk($rows, 500) as $chunk) {
                        Attendance::upsert($chunk, ['attendance_session_id', 'student_id'], ['status', 'remarks', 'updated_at']);
                    }
                }
            }
        }
    }
}