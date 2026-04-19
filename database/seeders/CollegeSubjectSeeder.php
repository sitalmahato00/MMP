<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class CollegeSubjectSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        $session = $this->collegeSession();

        foreach ($this->departmentCatalog() as $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();
            $program = Program::query()->where('code', $profile['program_code'])->first();

            if (! $department || ! $program) {
                continue;
            }

            $teachers = Teacher::query()
                ->where('department_id', $department->id)
                ->get()
                ->keyBy('employee_id');

            $primaryTeacher = $teachers->get($this->teacherEmployeeId($profile['department_code'], '001'));
            $secondaryTeacher = $teachers->get($this->teacherEmployeeId($profile['department_code'], '002'));

            for ($semester = 1; $semester <= self::SEMESTER_COUNT; $semester++) {
                foreach ($this->subjectDefinitions($profile, $semester) as $definition) {
                    $teacher = $definition['teacher_slot'] === 'secondary' ? $secondaryTeacher : $primaryTeacher;
                    $subjectCode = $this->subjectCode($program->code, $semester, $definition['slot']);
                    $subjectName = $this->subjectName($definition['theme'], $definition['label'], $semester);

                    $subject = Subject::query()->updateOrCreate(
                        ['code' => $subjectCode],
                        array_merge([
                            'program_id' => $program->id,
                            'semester' => $semester,
                            'name' => $subjectName,
                            'type' => $definition['type'],
                            'is_active' => true,
                        ], $definition['marks'])
                    );

                    if ($teacher) {
                        $subject->teachers()->syncWithoutDetaching([
                            $teacher->id => [
                                'academic_session_id' => $session->id,
                                'section' => 'A',
                            ],
                        ]);
                    }
                }
            }
        }
    }
}