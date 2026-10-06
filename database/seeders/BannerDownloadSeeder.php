<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Download;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class BannerDownloadSeeder extends Seeder
{
    public function run(): void
    {
        // ── Banners ──────────────────────────────────────────────────────────
        $banners = [
            [
                'title'       => 'Welcome to Manmohan Technical University',
                'subtitle'    => 'Excellence in Technical Education since 2065 BS',
                'image'       => 'banners/banner-1.jpg',
                'link'        => '/about',
                'button_text' => 'Learn More',
                'button_link' => '/about',
                'order'       => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'Admissions Open 2081-2082',
                'subtitle'    => 'CTEVT Affiliated Diploma Programs in IT, Civil, Electrical & Mechanical',
                'image'       => 'banners/banner-2.jpg',
                'link'        => '/admissions',
                'button_text' => 'Apply Now',
                'button_link' => '/admissions',
                'order'       => 2,
                'is_active'   => true,
            ],
            [
                'title'       => 'State-of-the-Art Laboratories',
                'subtitle'    => 'Modern labs equipped with the latest technology for hands-on learning',
                'image'       => 'banners/banner-3.jpg',
                'link'        => '/facilities',
                'button_text' => 'View Facilities',
                'button_link' => '/facilities',
                'order'       => 3,
                'is_active'   => true,
            ],
            [
                'title'       => 'Industry-Ready Graduates',
                'subtitle'    => '95% placement rate — our graduates work at top companies across Nepal',
                'image'       => 'banners/banner-4.jpg',
                'link'        => '/alumni',
                'button_text' => 'Meet Our Alumni',
                'button_link' => '/alumni',
                'order'       => 4,
                'is_active'   => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(['title' => $banner['title']], $banner);
        }

        // ── Downloads ────────────────────────────────────────────────────────
        $uploader = User::whereHas('roles', fn($q) => $q->whereIn('name', ['principal', 'admin']))->first();
        $itDept   = Department::where('code', 'IT')->first();
        $ceDept   = Department::where('code', 'CIVIL')->first();
        $eeDept   = Department::where('code', 'ELECTRICAL')->first();
        $meDept   = Department::where('code', 'MECHANICAL')->first();
        $ditProg  = Program::where('code', 'DIT')->first();
        $dceProg  = Program::where('code', 'DCE')->first();

        $downloads = [
            // College-wide forms
            [
                'title'       => 'Admission Form 2081-2082',
                'file_path'   => 'downloads/forms/admission-form-2081.pdf',
                'file_name'   => 'admission-form-2081.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 245760,
                'description' => 'Official admission application form for academic year 2081-2082. Fill and submit to the admission office.',
                'category'    => 'Forms',
                'is_public'   => true,
                'visibility'  => 'public',
            ],
            [
                'title'       => 'Scholarship Application Form 2081',
                'file_path'   => 'downloads/forms/scholarship-form-2081.pdf',
                'file_name'   => 'scholarship-form-2081.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 189440,
                'description' => 'Scholarship application form for meritorious and economically disadvantaged students.',
                'category'    => 'Forms',
                'is_public'   => true,
                'visibility'  => 'public',
            ],
            [
                'title'       => 'College Prospectus 2081-2082',
                'file_path'   => 'downloads/prospectus/prospectus-2081-2082.pdf',
                'file_name'   => 'mtu-prospectus-2081-2082.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 3145728,
                'description' => 'Complete college prospectus with program details, fee structure, facilities, and admission process.',
                'category'    => 'Prospectus',
                'is_public'   => true,
                'visibility'  => 'public',
            ],
            // IT Syllabi
            [
                'title'       => 'DIT Semester 1 Syllabus 2081',
                'file_path'   => 'downloads/syllabus/dit-sem1-2081.pdf',
                'file_name'   => 'dit-sem1-syllabus-2081.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 512000,
                'description' => 'CTEVT approved syllabus for DIT Semester 1, Academic Year 2081-2082.',
                'category'    => 'Syllabus',
                'department_id' => $itDept?->id,
                'program_id'  => $ditProg?->id,
                'semester'    => 1,
                'is_public'   => true,
                'visibility'  => 'public',
            ],
            [
                'title'       => 'DIT Semester 2 Syllabus 2081',
                'file_path'   => 'downloads/syllabus/dit-sem2-2081.pdf',
                'file_name'   => 'dit-sem2-syllabus-2081.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 524288,
                'description' => 'CTEVT approved syllabus for DIT Semester 2, Academic Year 2081-2082.',
                'category'    => 'Syllabus',
                'department_id' => $itDept?->id,
                'program_id'  => $ditProg?->id,
                'semester'    => 2,
                'is_public'   => true,
                'visibility'  => 'public',
            ],
            // Civil Syllabi
            [
                'title'       => 'DCE Semester 1 Syllabus 2081',
                'file_path'   => 'downloads/syllabus/dce-sem1-2081.pdf',
                'file_name'   => 'dce-sem1-syllabus-2081.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 489472,
                'description' => 'CTEVT approved syllabus for DCE Semester 1, Academic Year 2081-2082.',
                'category'    => 'Syllabus',
                'department_id' => $ceDept?->id,
                'program_id'  => $dceProg?->id,
                'semester'    => 1,
                'is_public'   => true,
                'visibility'  => 'public',
            ],
            // Study notes (students only)
            [
                'title'       => 'Computer Fundamentals - Complete Notes',
                'file_path'   => 'downloads/notes/it101-computer-fundamentals-notes.pdf',
                'file_name'   => 'computer-fundamentals-notes.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 2097152,
                'description' => 'Comprehensive notes for IT101 - Computer Fundamentals subject covering all units of the CTEVT syllabus.',
                'category'    => 'Notes',
                'department_id' => $itDept?->id,
                'program_id'  => $ditProg?->id,
                'semester'    => 1,
                'is_public'   => false,
                'visibility'  => 'students',
                'uploaded_by' => $uploader?->id,
            ],
            [
                'title'       => 'Programming in C - Practice Questions',
                'file_path'   => 'downloads/notes/it201-c-practice-questions.pdf',
                'file_name'   => 'c-programming-practice.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 1048576,
                'description' => 'Previous year questions and practice exercises for IT201 - Programming in C.',
                'category'    => 'Notes',
                'department_id' => $itDept?->id,
                'program_id'  => $ditProg?->id,
                'semester'    => 2,
                'is_public'   => false,
                'visibility'  => 'students',
                'uploaded_by' => $uploader?->id,
            ],
            [
                'title'       => 'DBMS Lab Manual',
                'file_path'   => 'downloads/notes/it302-dbms-lab-manual.pdf',
                'file_name'   => 'dbms-lab-manual.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 1572864,
                'description' => 'Complete lab manual for IT302 - Database Management System with MySQL exercises.',
                'category'    => 'Lab Manual',
                'department_id' => $itDept?->id,
                'program_id'  => $ditProg?->id,
                'semester'    => 3,
                'is_public'   => false,
                'visibility'  => 'students',
                'uploaded_by' => $uploader?->id,
            ],
            [
                'title'       => 'Examination Time Table - Semester 1 (2081)',
                'file_path'   => 'downloads/timetables/exam-timetable-sem1-2081.pdf',
                'file_name'   => 'exam-timetable-sem1-2081.pdf',
                'file_type'   => 'pdf',
                'file_size'   => 204800,
                'description' => 'CTEVT examination timetable for Semester 1, 2081. Applicable for all DIT, DCE, DEEE, DME students.',
                'category'    => 'Timetable',
                'is_public'   => true,
                'visibility'  => 'public',
                'uploaded_by' => $uploader?->id,
            ],
        ];

        foreach ($downloads as $download) {
            Download::firstOrCreate(
                ['title' => $download['title']],
                array_merge($download, ['uploaded_by' => $download['uploaded_by'] ?? $uploader?->id])
            );
        }

        $this->command->info('Banners and downloads seeded successfully.');
    }
}
