<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class CollegeDepartmentSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        $this->prepareForSeeding();

        foreach ($this->departmentCatalog() as $profile) {
            $department = Department::withTrashed()->updateOrCreate(
                ['code' => $profile['department_code']],
                [
                    'name' => $profile['department_name'],
                    'slug' => \Illuminate\Support\Str::slug($profile['department_name']),
                    'description' => $profile['department_description'],
                    'photo' => null,
                    'syllabus' => null,
                    'seat_capacity' => 60,
                    'is_active' => true,
                    'hod_id' => null,
                ]
            );

            $this->restoreIfTrashed($department);

            $hodUser = $this->seedUser($profile['hod_name'], $profile['hod_email'], 'hod');
            $department->update(['hod_id' => $hodUser->id]);
        }
    }
}