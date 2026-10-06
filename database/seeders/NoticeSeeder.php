<?php

namespace Database\Seeders;

use App\Models\Notice;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'principal'))->first();
        if (!$admin) {
            $this->command->warn('No principal user found — skipping NoticeSeeder.');
            return;
        }

        $notices = [
            // General Notices
            [
                'title'        => 'Admission Open for Academic Year 2081-2082',
                'type'         => 'general',
                'content'      => "Manmohan Technical University (MTU) is pleased to announce that admissions are now open for the academic year 2081-2082 BS. Interested candidates who have passed SEE with minimum C grade in Mathematics and Science are encouraged to apply.\n\nAvailable programs:\n- Diploma in Information Technology (DIT)\n- Diploma in Civil Engineering (DCE)\n- Diploma in Electrical Engineering (DEEE)\n- Diploma in Mechanical Engineering (DME)\n\nApplication Deadline: Shrawan 30, 2081\nEntrance Exam Date: Bhadra 5, 2081\n\nContact the admission office for more details.",
                'is_published'  => true,
                'is_popup'      => true,
                'popup_from'    => '2024-07-01',
                'popup_to'      => '2024-08-15',
                'published_at'  => now()->subDays(30),
            ],
            [
                'title'        => 'First Semester Examination Schedule 2081',
                'type'         => 'exam',
                'content'      => "The examination schedule for the First Semester CTEVT Examination 2081 has been released. All students of Semester 1 are hereby informed that the examinations will commence from Ashwin 15, 2081.\n\nImportant instructions:\n1. Bring your admit card on every exam day\n2. No electronic devices are allowed in the examination hall\n3. Students must arrive 30 minutes before the exam\n4. Results will be published within 60 days of the last exam\n\nFor the detailed schedule, please visit the notice board or download the PDF attachment.",
                'is_published'  => true,
                'published_at'  => now()->subDays(20),
            ],
            [
                'title'        => 'Annual Sports Week 2081',
                'type'         => 'event',
                'content'      => "MTU Annual Sports Week 2081 is being organized from Bhadra 20 to Bhadra 27, 2081. All students and staff are encouraged to participate.\n\nSports events include:\n- Football (Boys)\n- Volleyball (Boys & Girls)\n- Basketball (Boys & Girls)\n- Table Tennis\n- Badminton\n- Athletics (100m, 200m, 400m, Long Jump, High Jump)\n\nRegistration deadline: Bhadra 15, 2081\nContact your department sports coordinator to register.",
                'is_published'  => true,
                'published_at'  => now()->subDays(15),
            ],
            [
                'title'        => 'Library Timing Change Notice',
                'type'         => 'general',
                'content'      => "This is to inform all students and staff that the library timing has been revised effective from Shrawan 1, 2081.\n\nNew Library Hours:\n- Sunday to Friday: 7:00 AM – 6:00 PM\n- Saturday: 9:00 AM – 4:00 PM\n\nThe library will remain closed on public holidays as declared by the Government of Nepal.\n\nFor any queries, contact the library at library@mmp.edu.np",
                'is_published'  => true,
                'published_at'  => now()->subDays(10),
            ],
            [
                'title'        => 'Scholarship Program for Meritorious Students',
                'type'         => 'general',
                'content'      => "MTU is pleased to announce a scholarship program for meritorious and economically disadvantaged students for the academic year 2081-2082.\n\nScholarship Categories:\n1. Merit Scholarship: For students securing A+ in all subjects in previous semester\n2. Need-based Scholarship: For students from economically disadvantaged families\n3. Sports Scholarship: For students representing the college in national-level sports\n\nApplication forms are available at the administration office.\nDeadline for applications: Ashwin 30, 2081\n\nRequired documents: Mark sheet, Citizenship, Income certificate (for need-based)",
                'is_published'  => true,
                'published_at'  => now()->subDays(8),
            ],
            [
                'title'        => 'Holiday Notice – Dashain 2081',
                'type'         => 'general',
                'content'      => "This is to inform all students and staff that the college will remain closed for the Dashain festival from Ashwin 28 to Kartik 5, 2081.\n\nClasses will resume from Kartik 6, 2081 (Monday).\n\nWishing everyone a Happy Dashain!\n\nNote: Students with pending assignments must submit them before Ashwin 28, 2081.",
                'is_published'  => true,
                'is_popup'      => true,
                'popup_from'    => '2024-10-10',
                'popup_to'      => '2024-10-15',
                'published_at'  => now()->subDays(5),
            ],
            [
                'title'        => 'Second Assessment Marks Published',
                'type'         => 'exam',
                'content'      => "The marks of Second Monthly Assessment (Shrawan 2081) have been published for all programs and semesters.\n\nStudents can view their marks by:\n1. Logging into the student portal at student.mmp.edu.np\n2. Visiting the department office during office hours\n\nObjections/corrections must be submitted within 7 days of publication.\n\nContact your respective HOD for any discrepancies.",
                'is_published'  => true,
                'published_at'  => now()->subDays(3),
            ],
            [
                'title'        => 'Industrial Visit – IT Department',
                'type'         => 'event',
                'content'      => "The IT Department is organizing an Industrial Visit to Kathmandu Valley for Semester 3 and 4 students.\n\nVenue: IT Park Banepa, Yomari Tech Hub, and NIC Asia Digital Innovation Center\nDate: Bhadra 10-11, 2081\nTransportation: College bus provided\nCost: Rs. 500 per student (inclusive of entry fee and lunch)\n\nLast date for registration: Bhadra 5, 2081\nContact: Mr. Hari Prasad Kafle, IT Department",
                'is_published'  => true,
                'published_at'  => now()->subDays(2),
            ],
            [
                'title'        => 'CTEVT Affiliation Renewal Completed',
                'type'         => 'general',
                'content'      => "We are pleased to announce that MTU has successfully renewed its CTEVT affiliation for the academic year 2081-2082. All programs are fully approved and recognized by the Council for Technical Education and Vocational Training (CTEVT), Nepal.\n\nThis ensures our students will receive nationally recognized diplomas upon completion of their respective programs.\n\nFor verification of affiliation, visit: www.ctevt.org.np",
                'is_published'  => true,
                'published_at'  => now()->subDays(1),
            ],
            [
                'title'        => 'Student Council Election 2081',
                'type'         => 'event',
                'content'      => "The Student Council Election for the academic year 2081-2082 will be held on Bhadra 25, 2081.\n\nPositions open for election:\n- President (1)\n- Vice President (1)\n- Secretary (1)\n- Treasurer (1)\n- Department Representatives (1 per department)\n\nNomination forms available at the administration office from Bhadra 15 to Bhadra 20, 2081.\n\nAll regular students are eligible to vote. Voter ID will be your student ID card.",
                'is_published'  => true,
                'published_at'  => now(),
            ],
        ];

        foreach ($notices as $data) {
            $slug = Str::slug($data['title']);
            // Ensure unique slug
            $count = Notice::where('slug', 'like', $slug . '%')->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }

            Notice::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'slug'       => $slug,
                    'created_by' => $admin->id,
                ])
            );
        }

        $this->command->info('Notices seeded successfully (10 notices).');
    }
}
