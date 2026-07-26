<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Download;
use App\Models\Media;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * PublicPageSeeder — seeds data visible on the public-facing pages:
 *   - College-wide notices (department_id IS NULL) → home page notice board
 *   - Department-specific notices (department_id = X) → department pages
 *   - News & events → home + /news-events
 *   - Gallery media → /gallery + department gallery
 *   - Downloads → /downloads + department downloads sidebar
 */
class PublicPageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'sitalmahato077@gmail.com')->first()
            ?? User::where('email', 'hod@test.com')->first()
            ?? User::first();

        if (! $admin) {
            $this->command->warn('No user found — run DatabaseSeeder first.');
            return;
        }

        $departments = Department::where('is_active', true)->get()->keyBy('slug');

        $this->seedCollegeWideNotices($admin);
        $this->seedNewsAndEvents($admin);
        $this->seedDepartmentNotices($admin, $departments);
        $this->seedGalleryMedia($departments);
        $this->seedDownloads($admin, $departments);

        $this->command->info('');
        $this->command->info('=== PublicPageSeeder complete ===');
        $this->command->info('College-wide notices, news/events, gallery, and downloads seeded.');
    }

    // ── College-wide notices (home page notice board) ─────────────────────

    private function seedCollegeWideNotices(User $author): void
    {
        $notices = [
            [
                'title' => 'Admission Open for Academic Year 2082-2083',
                'type' => 'general',
                'days' => -1,
                'content' => 'Manmohan Memorial Polytechnic is pleased to announce that admissions are now open for the academic year 2082-2083. Applications are invited for Diploma programs in Information Technology, Civil, Electrical, Mechanical, and Electronics Engineering. Interested candidates may collect the prospectus from the college office or download it from our website.',
            ],
            [
                'title' => 'College Closed on Dashain and Tihar Holidays',
                'type' => 'general',
                'days' => -5,
                'content' => 'The college will remain closed from Ashwin 10 to Ashwin 20, 2081 on account of the Dashain festival. Classes will resume from Ashwin 21. Students are requested to complete their pending assignments before the holidays.',
            ],
            [
                'title' => 'CTEVT Practical Examination Schedule Published',
                'type' => 'exam',
                'days' => -2,
                'content' => 'The CTEVT practical examination schedule for the first semester has been published. Practical exams will be conducted from Poush 15 to Poush 25, 2081. Students must carry their college ID card and admit card on the day of the examination.',
            ],
            [
                'title' => 'Annual Academic Calendar 2081-2082 Released',
                'type' => 'general',
                'days' => -15,
                'content' => 'The annual academic calendar for the session 2081-2082 has been released. The calendar includes important dates for examinations, holidays, and academic activities. Students and staff are requested to note the dates accordingly.',
            ],
            [
                'title' => 'Notice Regarding Fee Submission Deadline',
                'type' => 'general',
                'days' => -3,
                'content' => 'All students are hereby informed that the deadline for tuition fee submission is Poush 30, 2081. Students who fail to submit the fee within the deadline will be charged a late fee of Rs. 100 per day. Please contact the accounts section for any queries.',
            ],
            [
                'title' => 'CTEVT Result of Third Semester Published',
                'type' => 'exam',
                'days' => -7,
                'content' => 'The results of the CTEVT Third Semester examination conducted in Bhadra 2081 have been published on the CTEVT official website (www.ctevt.org.np). Students are advised to check their results and contact the examination section for any discrepancies.',
            ],
            [
                'title' => 'Scholarship Application Form Available at Admin Office',
                'type' => 'general',
                'days' => -4,
                'content' => 'The CTEVT merit scholarship application forms for the session 2081-2082 are now available at the college administration office. Eligible students (above 75% attendance in previous semester and first division marks) may collect and submit the form before Magh 15, 2081.',
            ],
        ];

        $count = 0;
        foreach ($notices as $n) {
            $exists = Notice::where('title', $n['title'])->exists();
            if ($exists) continue;

            Notice::create([
                'title' => $n['title'],
                'slug' => Str::slug($n['title']) . '-' . Str::random(5),
                'content' => $n['content'],
                'type' => $n['type'],
                'department_id' => null,   // College-wide: shows on home page
                'program_id' => null,
                'semester' => null,
                'created_by' => $author->id,
                'is_published' => true,
                'published_at' => now()->addDays($n['days']),
            ]);
            $count++;
        }

        $this->command->info("College-wide notices seeded: {$count}");
    }

    // ── News & Events (home page + /news-events page) ─────────────────────

    private function seedNewsAndEvents(User $author): void
    {
        $items = [
            [
                'title' => 'MMP Hosts Annual Technical Exhibition 2081',
                'type' => 'news',
                'days' => -3,
                'content' => 'Manmohan Memorial Polytechnic successfully organized its Annual Technical Exhibition on Falgun 5, 2081. Students from all five departments showcased innovative projects including IoT-based home automation systems, structural models, and electronic circuits. The event was inaugurated by Chief Guest Er. Rajendra Thapa, Joint Secretary, Ministry of Education.',
            ],
            [
                'title' => 'MMP Students Excel in CTEVT Regional Skills Competition',
                'type' => 'news',
                'days' => -10,
                'content' => 'Students of Manmohan Memorial Polytechnic brought pride to the institution by securing top positions in the CTEVT Regional Technical Skills Competition held in Biratnagar. Sanjiv Thakur (IT Department) won the first prize in the Computer Hardware event while the Civil Engineering team secured second position in the Structural Drawing competition.',
            ],
            [
                'title' => 'Upcoming: Industry Visit to Biratnagar Industrial Zone',
                'type' => 'event',
                'days' => 5,
                'content' => 'A two-day industry visit to Biratnagar Industrial Zone is scheduled for Magh 20-21, 2081. Students from the Electrical and Mechanical Engineering departments will visit manufacturing units and interact with industry professionals. Participation is mandatory for third semester students. Registration deadline: Magh 15.',
            ],
            [
                'title' => 'MOU Signed with TechNepal for Student Internships',
                'type' => 'news',
                'days' => -8,
                'content' => 'Manmohan Memorial Polytechnic has signed a Memorandum of Understanding (MOU) with TechNepal Pvt. Ltd., a leading IT company based in Kathmandu. Under this agreement, final year IT and Electronics students will be eligible for three-month paid internships at TechNepal facilities.',
            ],
            [
                'title' => 'Guest Lecture: Career Opportunities in Technical Education',
                'type' => 'event',
                'days' => 3,
                'content' => 'The college is organizing a guest lecture on "Career Opportunities After Technical Diploma" on Magh 12, 2081. The speaker is Mr. Dipak Rai, Human Resources Manager at CG Electronics Nepal. All students are encouraged to attend. The lecture will be held in the main auditorium at 11:00 AM.',
            ],
        ];

        $count = 0;
        foreach ($items as $item) {
            $exists = Notice::where('title', $item['title'])->exists();
            if ($exists) continue;

            Notice::create([
                'title' => $item['title'],
                'slug' => Str::slug($item['title']) . '-' . Str::random(5),
                'content' => $item['content'],
                'type' => $item['type'],
                'department_id' => null,
                'program_id' => null,
                'semester' => null,
                'created_by' => $author->id,
                'is_published' => true,
                'published_at' => now()->addDays($item['days']),
            ]);
            $count++;
        }

        $this->command->info("News & events seeded: {$count}");
    }

    // ── Department-specific notices ───────────────────────────────────────

    private function seedDepartmentNotices(User $author, $departments): void
    {
        if ($departments->isEmpty()) {
            $this->command->warn('No active departments found — skipping department notices.');
            return;
        }

        $perDept = [
            [
                'title' => 'Department Internal Assessment Marks Published',
                'type' => 'exam',
                'days' => -1,
                'content' => 'The internal assessment marks for the current semester have been published on the department notice board. Students who wish to apply for re-checking may submit a written application to the department within 3 working days.',
            ],
            [
                'title' => 'Practical Class Schedule Changed for This Week',
                'type' => 'general',
                'days' => 0,
                'content' => 'Due to a special workshop on Friday, the practical classes scheduled for this week have been rescheduled. The revised schedule is available on the department notice board. Students are requested to check the updated timetable.',
            ],
            [
                'title' => 'Department HOD Meeting with Students — Feedback Session',
                'type' => 'general',
                'days' => 2,
                'content' => 'The Head of Department will be conducting a student feedback session on Magh 18, 2081 at 2:00 PM in the department seminar hall. All semester students are requested to be present. Issues related to academics, facilities, and welfare will be discussed.',
            ],
            [
                'title' => 'Lab Safety Guidelines — Mandatory Reading',
                'type' => 'department',
                'days' => -4,
                'content' => 'All students using the department laboratories are required to follow the updated safety guidelines issued by the college. Students found violating lab safety rules will face disciplinary action. The guidelines document is available at the department office.',
            ],
        ];

        $count = 0;
        foreach ($departments as $dept) {
            foreach ($perDept as $n) {
                $title = $n['title'] . ' — ' . $dept->code;
                $exists = Notice::where('title', $title)->exists();
                if ($exists) continue;

                Notice::create([
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . Str::random(5),
                    'content' => $n['content'],
                    'type' => $n['type'],
                    'department_id' => $dept->id,   // Department-specific
                    'program_id' => null,
                    'semester' => null,
                    'created_by' => $author->id,
                    'is_published' => true,
                    'published_at' => now()->addDays($n['days']),
                ]);
                $count++;
            }
        }

        $this->command->info("Department notices seeded: {$count}");
    }

    // ── Gallery media ─────────────────────────────────────────────────────

    private function seedGalleryMedia($departments): void
    {
        // We need a valid uploader — resolve once at the top of the method
        $uploader = \App\Models\User::first();
        if (! $uploader) {
            $this->command->warn('No user found for media uploader — skipping gallery.');
            return;
        }

        // College-wide gallery items (no department_id)
        $collegePhotos = [
            ['title' => 'College Main Building',           'file' => 'gallery/college-main.jpg'],
            ['title' => 'Annual Technical Exhibition 2081','file' => 'gallery/tech-exhibition.jpg'],
            ['title' => 'Graduation Ceremony 2080',        'file' => 'gallery/graduation-2080.jpg'],
            ['title' => 'Sports Day 2081',                 'file' => 'gallery/sports-day.jpg'],
            ['title' => 'Library Reading Hall',            'file' => 'gallery/library.jpg'],
            ['title' => 'Student Workshop Session',        'file' => 'gallery/workshop.jpg'],
        ];

        $count = 0;
        foreach ($collegePhotos as $photo) {
            $exists = Media::where('title', $photo['title'])->whereNull('department_id')->exists();
            if ($exists) continue;

            Media::create([
                'title'       => $photo['title'],
                'file_name'   => basename($photo['file']),
                'file_path'   => $photo['file'],
                'file_type'   => 'gallery',
                'mime_type'   => 'image/jpeg',
                'size'        => rand(100000, 800000),
                'department_id' => null,
                'uploaded_by' => $uploader->id,
            ]);
            $count++;
        }

        // Department-specific gallery
        $deptPhotos = [
            ['title' => 'Lab Practical Session',    'file' => 'gallery/dept-lab-practical.jpg'],
            ['title' => 'Department Workshop',       'file' => 'gallery/dept-workshop.jpg'],
            ['title' => 'Project Demonstration',     'file' => 'gallery/dept-project-demo.jpg'],
            ['title' => 'Field Visit Photo',         'file' => 'gallery/dept-field-visit.jpg'],
        ];

        foreach ($departments as $dept) {
            foreach ($deptPhotos as $photo) {
                $title = $photo['title'] . ' — ' . $dept->code;
                $exists = Media::where('title', $title)->where('department_id', $dept->id)->exists();
                if ($exists) continue;

                Media::create([
                    'title'       => $title,
                    'file_name'   => basename($photo['file']),
                    'file_path'   => $photo['file'],
                    'file_type'   => 'gallery',
                    'mime_type'   => 'image/jpeg',
                    'size'        => rand(100000, 800000),
                    'department_id' => $dept->id,
                    'uploaded_by' => $uploader->id,
                ]);
                $count++;
            }
        }

        $this->command->info("Gallery media seeded: {$count}");
    }

    // ── Downloads ─────────────────────────────────────────────────────────

    private function seedDownloads(User $uploader, $departments): void
    {
        // College-wide downloads (no department_id) — shown on home page recent downloads
        $collegeDownloads = [
            [
                'title' => 'College Prospectus 2082-2083',
                'category' => 'prospectus',
                'desc' => 'Complete college prospectus including program details, fee structure, and admission procedures.',
            ],
            [
                'title' => 'Academic Calendar 2081-2082',
                'category' => 'calendar',
                'desc' => 'Annual academic calendar with examination schedules, holidays, and important dates.',
            ],
            [
                'title' => 'Scholarship Application Form',
                'category' => 'form',
                'desc' => 'CTEVT merit scholarship application form for eligible students.',
            ],
            [
                'title' => 'College Rules and Regulations',
                'category' => 'circular',
                'desc' => 'Updated rules and regulations handbook for students and staff.',
            ],
        ];

        $count = 0;
        foreach ($collegeDownloads as $dl) {
            $exists = Download::where('title', $dl['title'])->whereNull('department_id')->exists();
            if ($exists) continue;

            Download::create([
                'title' => $dl['title'],
                'file_path' => 'downloads/placeholder.pdf',
                'file_name' => Str::slug($dl['title']) . '.pdf',
                'file_type' => 'pdf',
                'file_size' => rand(100000, 500000),
                'description' => $dl['desc'],
                'category' => $dl['category'],
                'department_id' => null,   // College-wide
                'is_public' => true,
                'visibility' => 'public',
                'uploaded_by' => $uploader->id,
            ]);
            $count++;
        }

        // Department-specific downloads — shown in department sidebar + /downloads?department=X
        $deptDownloads = [
            ['title' => 'Semester 1 Course Syllabus',     'category' => 'syllabus', 'desc' => 'Detailed semester 1 syllabus as per CTEVT curriculum.'],
            ['title' => 'Lab Manual and Guidelines',       'category' => 'lab_manual', 'desc' => 'Laboratory manual and safety guidelines for practical sessions.'],
            ['title' => 'Internal Assessment Schedule',    'category' => 'circular', 'desc' => 'Schedule for internal assessment examinations.'],
        ];

        foreach ($departments as $dept) {
            foreach ($deptDownloads as $dl) {
                $title = $dl['title'] . ' — ' . $dept->name;
                $exists = Download::where('title', $title)->where('department_id', $dept->id)->exists();
                if ($exists) continue;

                Download::create([
                    'title' => $title,
                    'file_path' => 'downloads/placeholder.pdf',
                    'file_name' => Str::slug($title) . '.pdf',
                    'file_type' => 'pdf',
                    'file_size' => rand(100000, 500000),
                    'description' => $dl['desc'],
                    'category' => $dl['category'],
                    'department_id' => $dept->id,
                    'is_public' => true,
                    'visibility' => 'public',
                    'uploaded_by' => $uploader->id,
                ]);
                $count++;
            }
        }

        $this->command->info("Downloads seeded: {$count}");
    }
}
