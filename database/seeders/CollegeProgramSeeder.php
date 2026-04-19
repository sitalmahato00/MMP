<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;

class CollegeProgramSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        foreach ($this->departmentCatalog() as $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();

            if (! $department) {
                continue;
            }

            $program = Program::withTrashed()->updateOrCreate(
                ['code' => $profile['program_code']],
                [
                    'department_id' => $department->id,
                    'name' => $profile['program_name'],
                    'slug' => \Illuminate\Support\Str::slug($profile['program_name']),
                    'total_semesters' => self::SEMESTER_COUNT,
                    'duration_years' => 3,
                    'description' => $profile['program_description'],
                    'is_active' => true,
                ]
            );

            $this->restoreIfTrashed($program);
        }
    }
}