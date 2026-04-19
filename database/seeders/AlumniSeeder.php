<?php

namespace Database\Seeders;

use App\Models\Alumni;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AlumniSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        $this->prepareForSeeding();

        foreach ($this->departmentCatalog() as $departmentIndex => $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();
            $program = Program::query()->where('code', $profile['program_code'])->first();

            if (! $department || ! $program) {
                continue;
            }

            for ($alumniIndex = 1; $alumniIndex <= 10; $alumniIndex++) {
                $user = $this->seedUser(
                    sprintf('%s Alumni %02d', $profile['program_code'], $alumniIndex),
                    $this->alumniEmail($profile['program_code'], $alumniIndex),
                    'alumni',
                    '96' . str_pad((string) ($departmentIndex * 10000 + $alumniIndex * 111), 8, '0', STR_PAD_LEFT)
                );

                $alumni = Alumni::withTrashed()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_id' => null,
                        'department_id' => $department->id,
                        'program_id' => $program->id,
                        'roll_number' => sprintf('%s-AL-%02d', $profile['alumni_prefix'], $alumniIndex),
                        'admission_year' => (string) (2074 + $alumniIndex),
                        'graduation_year' => (string) (2078 + $alumniIndex),
                        'current_job' => $this->alumniJobTitle($departmentIndex + 1, $alumniIndex),
                        'company_name' => $this->alumniCompany($departmentIndex + 1, $alumniIndex),
                        'work_location' => $this->alumniLocation($departmentIndex + 1),
                        'employment_status' => $alumniIndex % 3 === 0 ? 'studying' : 'employed',
                        'achievements' => sprintf('%s alumni showcase record %02d.', $profile['program_code'], $alumniIndex),
                        'bio' => sprintf('Seeded alumni profile for %s feature testing.', $profile['program_code']),
                        'skills' => $this->alumniSkills($profile['program_code'], $alumniIndex),
                        'linkedin_url' => 'https://linkedin.com/in/' . \Illuminate\Support\Str::slug(strtolower($user->name)),
                        'github_url' => 'https://github.com/' . \Illuminate\Support\Str::slug(strtolower($user->name)),
                        'portfolio_url' => 'https://example.com/' . \Illuminate\Support\Str::slug(strtolower($user->name)),
                        'profile_completion' => 100,
                        'visibility' => 'public',
                        'is_featured' => $alumniIndex === 1,
                        'is_verified' => true,
                    ]
                );

                $this->restoreIfTrashed($alumni);
            }
        }
    }
}