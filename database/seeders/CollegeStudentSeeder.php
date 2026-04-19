<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Database\Seeder;

class CollegeStudentSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        $session = $this->collegeSession();
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $sections = ['A', 'B', 'C'];

        foreach ($this->departmentCatalog() as $departmentIndex => $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();
            $program = Program::query()->where('code', $profile['program_code'])->first();

            if (! $department || ! $program) {
                continue;
            }

            for ($semester = 1; $semester <= self::SEMESTER_COUNT; $semester++) {
                for ($studentIndex = 1; $studentIndex <= self::STUDENTS_PER_SEMESTER; $studentIndex++) {
                    $user = $this->seedUser(
                        sprintf('%s Sem %d Student %02d', $profile['program_code'], $semester, $studentIndex),
                        $this->studentEmail($profile['program_code'], $semester, $studentIndex),
                        'student',
                        '98' . str_pad((string) ($departmentIndex * 10000 + $semester * 100 + $studentIndex), 8, '0', STR_PAD_LEFT)
                    );

                    $student = Student::unguarded(function () use ($user, $department, $program, $session, $profile, $semester, $studentIndex, $departmentIndex, $sections, $bloodGroups) {
                        return Student::withTrashed()->updateOrCreate(
                            ['student_no' => $this->studentNo($profile['program_code'], $semester, $studentIndex)],
                            [
                                'user_id' => $user->id,
                                'department_id' => $department->id,
                                'program_id' => $program->id,
                                'academic_session_id' => $session->id,
                                'roll_number' => $this->rollNumber($profile['program_code'], $semester, $studentIndex),
                                'registration_number' => sprintf('MMP-%s-%d-%03d', $profile['program_code'], $semester, $studentIndex),
                                'current_semester' => $semester,
                                'section' => $sections[($studentIndex - 1) % count($sections)],
                                'batch' => '2081',
                                'admission_date' => now()->subMonths(max(1, 18 - $semester))->toDateString(),
                                'guardian_name' => sprintf('%s Guardian %02d', $profile['program_code'], $studentIndex),
                                'guardian_phone' => '98' . str_pad((string) ($departmentIndex * 10000 + $semester * 100 + $studentIndex), 8, '0', STR_PAD_LEFT),
                                'blood_group' => $bloodGroups[($studentIndex + $semester + $departmentIndex) % count($bloodGroups)],
                                'status' => 'active',
                                'is_archived' => false,
                            ]
                        );
                    });

                    $this->restoreIfTrashed($student);
                }
            }
        }
    }
}