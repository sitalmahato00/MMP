<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Department;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->whereIn('code', ['IT', 'CE', 'AR', 'EL', 'EE', 'ME'])->get()->keyBy('code');

        $rows = [
            [
                'full_name' => 'Aastha Sharma',
                'email' => 'applicant01@mmp.edu.np',
                'phone' => '9841003101',
                'dob' => now()->subYears(18)->toDateString(),
                'gender' => 'female',
                'address' => 'Biratnagar, Morang',
                'guardian_name' => 'Ramesh Sharma',
                'guardian_phone' => '9841004101',
                'previous_school' => 'Everest Secondary School',
                'gpa' => '3.65',
                'department_id' => $departments['IT']->id ?? null,
                'message' => 'Interested in software and web development.',
                'status' => 'accepted',
                'admin_notes' => 'Strong academic record and interview performance.',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(12),
            ],
            [
                'full_name' => 'Rohan Karki',
                'email' => 'applicant02@mmp.edu.np',
                'phone' => '9841003102',
                'dob' => now()->subYears(19)->toDateString(),
                'gender' => 'male',
                'address' => 'Itahari, Sunsari',
                'guardian_name' => 'Sunita Karki',
                'guardian_phone' => '9841004102',
                'previous_school' => 'Shree Janata Secondary',
                'gpa' => '3.22',
                'department_id' => $departments['CE']->id ?? null,
                'message' => 'Wants to pursue civil infrastructure projects.',
                'status' => 'reviewed',
                'admin_notes' => 'Need to verify recommendation letter.',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(10),
            ],
            [
                'full_name' => 'Nima Sherpa',
                'email' => 'applicant03@mmp.edu.np',
                'phone' => '9841003103',
                'dob' => now()->subYears(18)->subMonths(6)->toDateString(),
                'gender' => 'other',
                'address' => 'Dharan, Sunsari',
                'guardian_name' => 'Lakpa Sherpa',
                'guardian_phone' => '9841004103',
                'previous_school' => 'Himalaya Academy',
                'gpa' => '3.48',
                'department_id' => $departments['EL']->id ?? null,
                'message' => 'Passionate about practical electrical systems.',
                'status' => 'contacted',
                'admin_notes' => 'Student has been contacted for counseling.',
                'created_at' => now()->subDays(9),
                'updated_at' => now()->subDays(8),
            ],
            [
                'full_name' => 'Prerna Bista',
                'email' => 'applicant04@mmp.edu.np',
                'phone' => '9841003104',
                'dob' => now()->subYears(19)->subMonths(2)->toDateString(),
                'gender' => 'female',
                'address' => 'Birtamod, Jhapa',
                'guardian_name' => 'Madan Bista',
                'guardian_phone' => '9841004104',
                'previous_school' => 'Kankai Model School',
                'gpa' => '3.09',
                'department_id' => $departments['AR']->id ?? null,
                'message' => 'Interested in architecture and drafting.',
                'status' => 'pending',
                'admin_notes' => null,
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(6),
            ],
            [
                'full_name' => 'Bibek Rai',
                'email' => 'applicant05@mmp.edu.np',
                'phone' => '9841003105',
                'dob' => now()->subYears(20)->toDateString(),
                'gender' => 'male',
                'address' => 'Dhankuta Bazaar',
                'guardian_name' => 'Sita Rai',
                'guardian_phone' => '9841004105',
                'previous_school' => 'Adarsha Higher Secondary',
                'gpa' => '2.84',
                'department_id' => $departments['ME']->id ?? null,
                'message' => 'Applying for mechanical diploma.',
                'status' => 'rejected',
                'admin_notes' => 'Did not meet minimum eligibility criteria.',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(2),
            ],
        ];

        foreach ($rows as $row) {
            Application::query()->updateOrCreate(
                ['email' => $row['email']],
                $row
            );
        }
    }
}
