<?php

namespace Database\Seeders;

use App\Models\Alumni;
use App\Models\AlumniAchievement;
use App\Models\AlumniEmployment;
use App\Models\AlumniProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlumniSeeder extends Seeder
{
    public function run(): void
    {
        $alumniData = [
            [
                'name' => 'Sita Sharma',
                'email' => 'sita@alumni.mmp.edu',
                'phone' => '9841234567',
                'department_id' => 1,
                'program_id' => 1,
                'graduation_year' => '2082',
                'admission_year' => '2078',
                'roll_number' => '078-DIT-001',
                'current_job' => 'Software Engineer',
                'company_name' => 'Leapfrog Technology',
                'work_location' => 'Kathmandu, Nepal',
                'employment_status' => 'employed',
                'bio' => 'Passionate full-stack developer with experience in Laravel, React, and cloud technologies. Graduated top of the class with distinction.',
                'skills' => ['PHP', 'Laravel', 'React', 'MySQL', 'Docker', 'AWS'],
                'linkedin_url' => 'https://linkedin.com/in/sitasharma',
                'github_url' => 'https://github.com/sitasharma',
                'is_featured' => true,
                'is_verified' => true,
                'visibility' => 'public',
            ],
            [
                'name' => 'Hari Bahadur Thapa',
                'email' => 'hari@alumni.mmp.edu',
                'phone' => '9851234568',
                'department_id' => 1,
                'program_id' => 1,
                'graduation_year' => '2081',
                'admission_year' => '2077',
                'roll_number' => '077-DIT-015',
                'current_job' => 'DevOps Engineer',
                'company_name' => 'Cotiviti Nepal',
                'work_location' => 'Lalitpur, Nepal',
                'employment_status' => 'employed',
                'bio' => 'Infrastructure and DevOps specialist. Love automating everything.',
                'skills' => ['Linux', 'Docker', 'Kubernetes', 'Terraform', 'Python'],
                'github_url' => 'https://github.com/haribthapa',
                'is_featured' => true,
                'is_verified' => true,
                'visibility' => 'public',
            ],
            [
                'name' => 'Anita Gurung',
                'email' => 'anita@alumni.mmp.edu',
                'phone' => '9861234569',
                'department_id' => 3,
                'program_id' => 3,
                'graduation_year' => '2082',
                'admission_year' => '2078',
                'roll_number' => '078-DCE-008',
                'current_job' => 'Site Engineer',
                'company_name' => 'NCC Pvt. Ltd.',
                'work_location' => 'Pokhara, Nepal',
                'employment_status' => 'employed',
                'bio' => 'Civil engineer passionate about sustainable construction and green building design.',
                'skills' => ['AutoCAD', 'Revit', 'SAP2000', 'Project Management'],
                'is_featured' => false,
                'is_verified' => true,
                'visibility' => 'public',
            ],
            [
                'name' => 'Raj Kumar Yadav',
                'email' => 'raj@alumni.mmp.edu',
                'phone' => '9871234570',
                'department_id' => 4,
                'program_id' => 4,
                'graduation_year' => '2081',
                'admission_year' => '2077',
                'roll_number' => '077-DEE-022',
                'current_job' => null,
                'company_name' => null,
                'employment_status' => 'studying',
                'bio' => 'Currently pursuing B.E. in Electrical Engineering at IOE Pulchowk. Interested in renewable energy systems.',
                'skills' => ['MATLAB', 'PLC Programming', 'Circuit Design'],
                'is_featured' => false,
                'is_verified' => true,
                'visibility' => 'public',
            ],
            [
                'name' => 'Priya Maharjan',
                'email' => 'priya@alumni.mmp.edu',
                'phone' => '9801234571',
                'department_id' => 1,
                'program_id' => 1,
                'graduation_year' => '2080',
                'admission_year' => '2076',
                'roll_number' => '076-DIT-030',
                'current_job' => 'UI/UX Designer',
                'company_name' => 'Fusemachines',
                'work_location' => 'Kathmandu, Nepal',
                'employment_status' => 'employed',
                'bio' => 'Creative designer bridging technology and user experience. Advocate for accessibility in design.',
                'skills' => ['Figma', 'Adobe XD', 'HTML/CSS', 'JavaScript', 'Tailwind CSS'],
                'linkedin_url' => 'https://linkedin.com/in/priyamaharjan',
                'portfolio_url' => 'https://priyamaharjan.com.np',
                'is_featured' => true,
                'is_verified' => true,
                'visibility' => 'public',
            ],
        ];

        foreach ($alumniData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);
            $user->assignRole('alumni');

            $alumnus = Alumni::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'program_id' => $data['program_id'],
                'graduation_year' => $data['graduation_year'],
                'admission_year' => $data['admission_year'] ?? null,
                'roll_number' => $data['roll_number'] ?? null,
                'current_job' => $data['current_job'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'work_location' => $data['work_location'] ?? null,
                'employment_status' => $data['employment_status'] ?? 'unknown',
                'bio' => $data['bio'] ?? null,
                'skills' => $data['skills'] ?? [],
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'github_url' => $data['github_url'] ?? null,
                'portfolio_url' => $data['portfolio_url'] ?? null,
                'is_featured' => $data['is_featured'] ?? false,
                'is_verified' => $data['is_verified'] ?? false,
                'visibility' => $data['visibility'] ?? 'public',
            ]);

            // Add employment history for employed alumni
            if ($data['employment_status'] === 'employed' && $data['current_job']) {
                AlumniEmployment::create([
                    'alumni_id' => $alumnus->id,
                    'job_title' => $data['current_job'],
                    'company_name' => $data['company_name'],
                    'location' => $data['work_location'],
                    'start_date' => now()->subMonths(rand(6, 24)),
                    'is_current' => true,
                    'description' => 'Currently working here.',
                ]);
            }
        }

        // Add projects to Sita (first alumni)
        $sita = Alumni::whereHas('user', fn($q) => $q->where('email', 'sita@alumni.mmp.edu'))->first();
        if ($sita) {
            AlumniProject::create([
                'alumni_id' => $sita->id,
                'type' => 'minor',
                'title' => 'Library Management System',
                'description' => 'A web-based library management system built with Laravel and Bootstrap. Features book catalog, borrowing management, and fine calculation.',
                'supervisor' => 'Er. Ram Prasad Adhikari',
                'technologies' => ['Laravel', 'MySQL', 'Bootstrap', 'jQuery'],
                'team_members' => ['Sita Sharma', 'Gita Devi'],
                'status' => 'completed',
                'is_visible' => true,
                'year' => '2080',
            ]);

            AlumniProject::create([
                'alumni_id' => $sita->id,
                'type' => 'major',
                'title' => 'Smart College ERP System',
                'description' => 'A comprehensive college management ERP system with modules for attendance, marks, timetable, and communication. Built as a modern SaaS application.',
                'supervisor' => 'Er. Bikash Shrestha',
                'technologies' => ['Laravel 11', 'React', 'Tailwind CSS', 'MySQL', 'Redis'],
                'team_members' => ['Sita Sharma', 'Hari Thapa', 'Anita Gurung'],
                'github_url' => 'https://github.com/sitasharma/college-erp',
                'demo_url' => 'https://college-erp.example.com',
                'status' => 'completed',
                'is_visible' => true,
                'year' => '2082',
            ]);

            AlumniAchievement::create([
                'alumni_id' => $sita->id,
                'title' => 'Best Project Award 2082',
                'description' => 'Awarded best final year project in IT department.',
                'year' => '2082',
            ]);

            AlumniAchievement::create([
                'alumni_id' => $sita->id,
                'title' => 'LOCUS Hackathon Runner-up',
                'description' => 'Second place at LOCUS 2081 hackathon organized by IOE Pulchowk.',
                'year' => '2081',
            ]);
        }

        // Add project to Priya
        $priya = Alumni::whereHas('user', fn($q) => $q->where('email', 'priya@alumni.mmp.edu'))->first();
        if ($priya) {
            AlumniProject::create([
                'alumni_id' => $priya->id,
                'type' => 'major',
                'title' => 'HealthTrack Nepal - Mobile Health App',
                'description' => 'A cross-platform mobile application for tracking health metrics and connecting patients with nearby health posts in rural Nepal.',
                'supervisor' => 'Er. Sunita KC',
                'technologies' => ['React Native', 'Node.js', 'MongoDB', 'Google Maps API'],
                'team_members' => ['Priya Maharjan', 'Bikram Shahi'],
                'status' => 'completed',
                'is_visible' => true,
                'year' => '2080',
            ]);

            // Employment history
            AlumniEmployment::create([
                'alumni_id' => $priya->id,
                'job_title' => 'Junior Designer',
                'company_name' => 'YoungInnovations',
                'location' => 'Kathmandu',
                'start_date' => now()->subYears(2),
                'end_date' => now()->subMonths(8),
                'is_current' => false,
                'description' => 'Designed UI for multiple civic-tech projects.',
            ]);
        }
    }
}
