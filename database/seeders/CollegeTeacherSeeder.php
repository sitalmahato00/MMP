<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class CollegeTeacherSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        foreach ($this->departmentCatalog() as $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();

            if (! $department) {
                continue;
            }

            $teachers = [
                [
                    'name' => $profile['teacher_primary_name'],
                    'email' => $profile['teacher_primary_email'],
                    'employee_id' => $this->teacherEmployeeId($profile['department_code'], '001'),
                    'designation' => 'Lecturer',
                    'qualification' => 'Permanent Faculty',
                    'specialization' => 'Core academic delivery and semester coordination.',
                ],
                [
                    'name' => $profile['teacher_secondary_name'],
                    'email' => $profile['teacher_secondary_email'],
                    'employee_id' => $this->teacherEmployeeId($profile['department_code'], '002'),
                    'designation' => 'Instructor',
                    'qualification' => 'Practical Faculty',
                    'specialization' => 'Lab supervision, mark verification, and class attendance.',
                ],
            ];

            foreach ($teachers as $teacherData) {
                $user = $this->seedUser($teacherData['name'], $teacherData['email'], 'teacher');

                $teacher = Teacher::withTrashed()->updateOrCreate(
                    ['employee_id' => $teacherData['employee_id']],
                    [
                        'user_id' => $user->id,
                        'department_id' => $department->id,
                        'designation' => $teacherData['designation'],
                        'qualification' => $teacherData['qualification'],
                        'specialization' => $teacherData['specialization'],
                        'join_date' => now()->subYears(2)->toDateString(),
                        'employment_type' => 'permanent',
                        'is_active' => true,
                    ]
                );

                $this->restoreIfTrashed($teacher);
            }
        }
    }
}