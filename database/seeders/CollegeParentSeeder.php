<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Database\Seeder;

class CollegeParentSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        foreach ($this->departmentCatalog() as $departmentIndex => $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();
            $program = Program::query()->where('code', $profile['program_code'])->first();

            if (! $department || ! $program) {
                continue;
            }

            for ($semester = 1; $semester <= self::SEMESTER_COUNT; $semester++) {
                $students = Student::query()
                    ->where('department_id', $department->id)
                    ->where('program_id', $program->id)
                    ->where('current_semester', $semester)
                    ->get();

                foreach ($students as $studentIndex => $student) {
                    $user = $this->seedUser(
                        sprintf('%s Guardian %02d', $profile['program_code'], $studentIndex + 1),
                        $this->parentEmail($profile['program_code'], $semester, $studentIndex + 1),
                        'parent',
                        '97' . str_pad((string) ($departmentIndex * 10000 + $semester * 100 + $studentIndex + 1), 8, '0', STR_PAD_LEFT)
                    );

                    $parent = ParentModel::query()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'occupation' => $this->guardianOccupation($departmentIndex + 1, $studentIndex + 1),
                            'relation_to_student' => (($studentIndex + $semester) % 2 === 0) ? 'mother' : 'father',
                        ]
                    );

                    $parent->children()->syncWithoutDetaching([$student->id]);
                }
            }
        }
    }
}