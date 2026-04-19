<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Communication;
use App\Models\Department;
use App\Models\Download;
use App\Models\Exam;
use App\Models\Executive;
use App\Models\Facility;
use App\Models\Mark;
use App\Models\Media;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Models\Page;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\StaffAttendance;
use App\Models\StaffDocument;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Services\PublicDataService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $assets = $this->seedAssets();

        DB::transaction(function () use ($assets) {
            foreach (['principal', 'hod', 'teacher', 'student', 'parent', 'alumni'] as $role) {
                Role::firstOrCreate([
                    'name' => $role,
                    'guard_name' => 'web',
                ]);
            }

            $principal = $this->seedUser('Dr. Principal', 'principal@mmp.edu.np', 'principal');
            $hod = $this->seedUser('Er. Yubraj Chaudhary', 'hod.it@mmp.edu.np', 'hod');
            $teacherUser = $this->seedUser('Er. Anil Khatri', 'teacher.it@mmp.edu.np', 'teacher');
            $studentOneUser = $this->seedUser('Sita Karki', 'student01@mmp.edu.np', 'student');
            $studentTwoUser = $this->seedUser('Rajan Thapa', 'student02@mmp.edu.np', 'student');
            $parentUser = $this->seedUser('Gita Karki', 'parent01@mmp.edu.np', 'parent');
            $alumniUser = $this->seedUser('Dipesh Shrestha', 'alumni01@mmp.edu.np', 'alumni');

            $session = AcademicSession::query()->updateOrCreate(
                ['name' => '2081-2082'],
                [
                    'name_bs' => '2081-2082',
                    'start_date' => now()->subMonths(2)->toDateString(),
                    'end_date' => now()->addMonths(10)->toDateString(),
                    'is_active' => true,
                    'status' => 'active',
                    'is_locked' => false,
                    'activated_at' => now(),
                    'ended_at' => null,
                    'notes' => 'Seeded demo academic session for MMP.',
                ]
            );

            $department = Department::withTrashed()->updateOrCreate(
                ['code' => 'IT'],
                [
                    'name' => 'Information Technology',
                    'slug' => Str::slug('Information Technology'),
                    'description' => 'Department of Information Technology.',
                    'photo' => null,
                    'syllabus' => null,
                    'seat_capacity' => 40,
                    'hod_id' => $hod->id,
                    'is_active' => true,
                ]
            );
            $this->restoreIfTrashed($department);

            $program = Program::withTrashed()->updateOrCreate(
                ['code' => 'DIT'],
                [
                    'department_id' => $department->id,
                    'name' => 'Diploma in Information Technology',
                    'slug' => Str::slug('Diploma in Information Technology'),
                    'total_semesters' => 6,
                    'duration_years' => 3,
                    'description' => 'Three-year diploma program in Information Technology.',
                    'is_active' => true,
                ]
            );
            $this->restoreIfTrashed($program);

            $departmentProfiles = [
                [
                    'department_code' => 'AR',
                    'department_name' => 'Architecture Engineering',
                    'program_code' => 'DAE',
                    'program_name' => 'Diploma in Architecture Engineering',
                    'description' => 'Architectural design, drafting, building planning and construction management.',
                    'hod_name' => 'Ar. Sushma Maharjan',
                    'hod_email' => 'hod.ar@mmp.edu.np',
                    'teacher_name' => 'Ar. Prabin Karki',
                    'teacher_email' => 'teacher.ar@mmp.edu.np',
                    'teacher_employee_id' => 'T-AR-001',
                    'teacher_designation' => 'Lecturer',
                    'teacher_qualification' => 'Bachelor of Architecture',
                    'teacher_specialization' => 'Studio Design and CAD',
                    'student_name' => 'Aarav Shrestha',
                    'student_email' => 'student.ar@mmp.edu.np',
                    'student_roll_number' => 'DAE-081-01',
                    'student_registration_number' => 'MMP-DAE-081-001',
                    'student_current_semester' => 4,
                    'student_section' => 'A',
                    'student_batch' => '2081',
                    'guardian_name' => 'Mina Shrestha',
                    'guardian_phone' => '9841000201',
                    'blood_group' => 'O+',
                    'parent_name' => 'Mina Shrestha',
                    'parent_email' => 'parent.ar@mmp.edu.np',
                    'parent_occupation' => 'Architect',
                    'alumni_name' => 'Sushil Neupane',
                    'alumni_email' => 'alumni.ar@mmp.edu.np',
                    'graduation_year' => '2080',
                    'current_job' => 'Junior Drafter',
                    'company_name' => 'Architect Studio Nepal',
                    'achievements' => 'Won the campus design competition.',
                    'notice_title' => 'Architecture Department Studio Review',
                    'notice_slug' => 'architecture-department-studio-review',
                    'notice_content' => 'Semester review for architecture students includes portfolio display and model critique.',
                ],
                [
                    'department_code' => 'CE',
                    'department_name' => 'Civil Engineering',
                    'program_code' => 'DCE',
                    'program_name' => 'Diploma in Civil Engineering',
                    'description' => 'Design, construction and maintenance of infrastructure including roads, bridges and buildings.',
                    'hod_name' => 'Er. Rajesh Joshi',
                    'hod_email' => 'hod.ce@mmp.edu.np',
                    'teacher_name' => 'Er. Nabin Khatri',
                    'teacher_email' => 'teacher.ce@mmp.edu.np',
                    'teacher_employee_id' => 'T-CE-001',
                    'teacher_designation' => 'Lecturer',
                    'teacher_qualification' => 'BE Civil Engineering',
                    'teacher_specialization' => 'Surveying and Structures',
                    'student_name' => 'Pragya KC',
                    'student_email' => 'student.ce@mmp.edu.np',
                    'student_roll_number' => 'DCE-081-01',
                    'student_registration_number' => 'MMP-DCE-081-001',
                    'student_current_semester' => 4,
                    'student_section' => 'A',
                    'student_batch' => '2081',
                    'guardian_name' => 'Geeta KC',
                    'guardian_phone' => '9841000202',
                    'blood_group' => 'A+',
                    'parent_name' => 'Geeta KC',
                    'parent_email' => 'parent.ce@mmp.edu.np',
                    'parent_occupation' => 'Contractor',
                    'alumni_name' => 'Milan Shrestha',
                    'alumni_email' => 'alumni.ce@mmp.edu.np',
                    'graduation_year' => '2080',
                    'current_job' => 'Site Supervisor',
                    'company_name' => 'Himalayan Infra Pvt. Ltd.',
                    'achievements' => 'Led the campus drainage layout project.',
                    'notice_title' => 'Civil Department Field Survey',
                    'notice_slug' => 'civil-department-field-survey',
                    'notice_content' => 'Civil students will visit the field site for surveying practice and bridge inspection.',
                ],
                [
                    'department_code' => 'EL',
                    'department_name' => 'Electrical Engineering',
                    'program_code' => 'DEL',
                    'program_name' => 'Diploma in Electrical Engineering',
                    'description' => 'Electrical systems, power generation, wiring, switchgear and electrical installations.',
                    'hod_name' => 'Er. Sarita Gurung',
                    'hod_email' => 'hod.el@mmp.edu.np',
                    'teacher_name' => 'Er. Suman Bista',
                    'teacher_email' => 'teacher.el@mmp.edu.np',
                    'teacher_employee_id' => 'T-EL-001',
                    'teacher_designation' => 'Instructor',
                    'teacher_qualification' => 'BE Electrical Engineering',
                    'teacher_specialization' => 'Power Systems',
                    'student_name' => 'Nisha Karki',
                    'student_email' => 'student.el@mmp.edu.np',
                    'student_roll_number' => 'DEL-081-01',
                    'student_registration_number' => 'MMP-DEL-081-001',
                    'student_current_semester' => 3,
                    'student_section' => 'A',
                    'student_batch' => '2081',
                    'guardian_name' => 'Bimala Karki',
                    'guardian_phone' => '9841000203',
                    'blood_group' => 'B+',
                    'parent_name' => 'Bimala Karki',
                    'parent_email' => 'parent.el@mmp.edu.np',
                    'parent_occupation' => 'Technician',
                    'alumni_name' => 'Suresh Tamang',
                    'alumni_email' => 'alumni.el@mmp.edu.np',
                    'graduation_year' => '2080',
                    'current_job' => 'Electrical Assistant',
                    'company_name' => 'Energy Works Nepal',
                    'achievements' => 'Recognized for safety leadership in lab practice.',
                    'notice_title' => 'Electrical Lab Maintenance Notice',
                    'notice_slug' => 'electrical-lab-maintenance-notice',
                    'notice_content' => 'Power lab maintenance will be completed before practical classes resume.',
                ],
                [
                    'department_code' => 'EE',
                    'department_name' => 'Electronics Engineering',
                    'program_code' => 'DEE',
                    'program_name' => 'Diploma in Electronics Engineering',
                    'description' => 'Electronics circuits, communication systems, embedded systems and signal processing.',
                    'hod_name' => 'Er. Pooja Adhikari',
                    'hod_email' => 'hod.ee@mmp.edu.np',
                    'teacher_name' => 'Er. Bikash Dhungana',
                    'teacher_email' => 'teacher.ee@mmp.edu.np',
                    'teacher_employee_id' => 'T-EE-001',
                    'teacher_designation' => 'Lecturer',
                    'teacher_qualification' => 'BE Electronics and Communication',
                    'teacher_specialization' => 'Embedded Systems',
                    'student_name' => 'Anisha Rai',
                    'student_email' => 'student.ee@mmp.edu.np',
                    'student_roll_number' => 'DEE-081-01',
                    'student_registration_number' => 'MMP-DEE-081-001',
                    'student_current_semester' => 3,
                    'student_section' => 'A',
                    'student_batch' => '2081',
                    'guardian_name' => 'Laxmi Rai',
                    'guardian_phone' => '9841000204',
                    'blood_group' => 'AB+',
                    'parent_name' => 'Laxmi Rai',
                    'parent_email' => 'parent.ee@mmp.edu.np',
                    'parent_occupation' => 'Business',
                    'alumni_name' => 'Nabin Thapa',
                    'alumni_email' => 'alumni.ee@mmp.edu.np',
                    'graduation_year' => '2080',
                    'current_job' => 'Electronics Technician',
                    'company_name' => 'Smart Device Lab',
                    'achievements' => 'Built a smart attendance prototype for the college expo.',
                    'notice_title' => 'Electronics Project Showcase',
                    'notice_slug' => 'electronics-project-showcase',
                    'notice_content' => 'Electronics students will present microcontroller projects in the lab hall.',
                ],
                [
                    'department_code' => 'ME',
                    'department_name' => 'Mechanical Engineering',
                    'program_code' => 'DME',
                    'program_name' => 'Diploma in Mechanical Engineering',
                    'description' => 'Machine design, manufacturing, thermodynamics and mechanical systems.',
                    'hod_name' => 'Er. Krishna Bhandari',
                    'hod_email' => 'hod.me@mmp.edu.np',
                    'teacher_name' => 'Er. Rabindra Kafle',
                    'teacher_email' => 'teacher.me@mmp.edu.np',
                    'teacher_employee_id' => 'T-ME-001',
                    'teacher_designation' => 'Lecturer',
                    'teacher_qualification' => 'BE Mechanical Engineering',
                    'teacher_specialization' => 'Workshop Technology',
                    'student_name' => 'Saurav Paudel',
                    'student_email' => 'student.me@mmp.edu.np',
                    'student_roll_number' => 'DME-081-01',
                    'student_registration_number' => 'MMP-DME-081-001',
                    'student_current_semester' => 4,
                    'student_section' => 'A',
                    'student_batch' => '2081',
                    'guardian_name' => 'Manju Paudel',
                    'guardian_phone' => '9841000205',
                    'blood_group' => 'A-',
                    'parent_name' => 'Manju Paudel',
                    'parent_email' => 'parent.me@mmp.edu.np',
                    'parent_occupation' => 'Workshop Supervisor',
                    'alumni_name' => 'Pratik Shrestha',
                    'alumni_email' => 'alumni.me@mmp.edu.np',
                    'graduation_year' => '2080',
                    'current_job' => 'Mechanical Assistant',
                    'company_name' => 'MMP Workshop Partners',
                    'achievements' => 'Completed the national skill competition project.',
                    'notice_title' => 'Mechanical Workshop Drive',
                    'notice_slug' => 'mechanical-workshop-drive',
                    'notice_content' => 'Workshop practice will focus on tooling, fitting, and safety demonstrations this week.',
                ],
            ];

            foreach ($departmentProfiles as $profile) {
                $this->seedDepartmentProfile($profile, $session, $assets);
            }

            $subjects = [];
            $subjectDefinitions = [
                [
                    'code' => 'CG501',
                    'name' => 'Computer Graphics',
                    'semester' => 5,
                    'type' => 'both',
                    'full_marks_internal_theory' => 20,
                    'full_marks_external_theory' => 80,
                    'pass_marks_internal_theory' => 8,
                    'pass_marks_external_theory' => 32,
                    'full_marks_internal_practical' => 30,
                    'full_marks_external_practical' => 20,
                    'pass_marks_internal_practical' => 15,
                    'pass_marks_external_practical' => 10,
                    'credit_hours' => 4,
                    'is_active' => true,
                ],
                [
                    'code' => 'WT502',
                    'name' => 'Web Technology I',
                    'semester' => 5,
                    'type' => 'theory',
                    'full_marks_internal_theory' => 20,
                    'full_marks_external_theory' => 80,
                    'pass_marks_internal_theory' => 8,
                    'pass_marks_external_theory' => 32,
                    'full_marks_internal_practical' => 0,
                    'full_marks_external_practical' => 0,
                    'pass_marks_internal_practical' => 0,
                    'pass_marks_external_practical' => 0,
                    'credit_hours' => 4,
                    'is_active' => true,
                ],
                [
                    'code' => 'DBMS503',
                    'name' => 'Database Management Systems',
                    'semester' => 5,
                    'type' => 'theory',
                    'full_marks_internal_theory' => 20,
                    'full_marks_external_theory' => 80,
                    'pass_marks_internal_theory' => 8,
                    'pass_marks_external_theory' => 32,
                    'full_marks_internal_practical' => 0,
                    'full_marks_external_practical' => 0,
                    'pass_marks_internal_practical' => 0,
                    'pass_marks_external_practical' => 0,
                    'credit_hours' => 4,
                    'is_active' => true,
                ],
            ];

            foreach ($subjectDefinitions as $definition) {
                $subject = Subject::query()->updateOrCreate(
                    ['code' => $definition['code']],
                    array_merge($definition, ['program_id' => $program->id])
                );
                $subjects[$definition['code']] = $subject;
            }

            $teacher = Teacher::withTrashed()->updateOrCreate(
                ['employee_id' => 'T-001'],
                [
                    'user_id' => $teacherUser->id,
                    'department_id' => $department->id,
                    'designation' => 'Lecturer',
                    'qualification' => 'BSc CSIT',
                    'specialization' => 'Web and Database Systems',
                    'join_date' => now()->subYears(2)->toDateString(),
                    'employment_type' => 'permanent',
                    'is_active' => true,
                ]
            );
            $this->restoreIfTrashed($teacher);

            $studentOne = Student::withTrashed()->updateOrCreate(
                ['academic_session_id' => $session->id, 'roll_number' => 'DIT-081-01'],
                [
                    'user_id' => $studentOneUser->id,
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'registration_number' => 'MMP-DIT-081-001',
                    'current_semester' => 5,
                    'section' => 'A',
                    'batch' => '2081',
                    'admission_date' => now()->subMonths(1)->toDateString(),
                    'guardian_name' => 'Gita Karki',
                    'guardian_phone' => '9841000001',
                    'blood_group' => 'A+',
                    'status' => 'active',
                    'is_archived' => false,
                ]
            );
            $this->restoreIfTrashed($studentOne);

            $studentTwo = Student::withTrashed()->updateOrCreate(
                ['academic_session_id' => $session->id, 'roll_number' => 'DIT-081-02'],
                [
                    'user_id' => $studentTwoUser->id,
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'registration_number' => 'MMP-DIT-081-002',
                    'current_semester' => 5,
                    'section' => 'A',
                    'batch' => '2081',
                    'admission_date' => now()->subMonths(1)->toDateString(),
                    'guardian_name' => 'Gita Karki',
                    'guardian_phone' => '9841000001',
                    'blood_group' => 'B+',
                    'status' => 'active',
                    'is_archived' => false,
                ]
            );
            $this->restoreIfTrashed($studentTwo);

            $studentThreeUser = $this->seedUser('Mina Gurung', 'student03@mmp.edu.np', 'student');
            $studentFourUser = $this->seedUser('Prakash Rai', 'student04@mmp.edu.np', 'student');

            $studentThree = Student::withTrashed()->updateOrCreate(
                ['academic_session_id' => $session->id, 'roll_number' => 'DIT-081-03'],
                [
                    'user_id' => $studentThreeUser->id,
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'registration_number' => 'MMP-DIT-081-003',
                    'current_semester' => 5,
                    'section' => 'A',
                    'batch' => '2081',
                    'admission_date' => now()->subMonths(1)->toDateString(),
                    'guardian_name' => 'Dhan Maya Gurung',
                    'guardian_phone' => '9841000003',
                    'blood_group' => 'O+',
                    'status' => 'active',
                    'is_archived' => false,
                ]
            );
            $this->restoreIfTrashed($studentThree);

            $studentFour = Student::withTrashed()->updateOrCreate(
                ['academic_session_id' => $session->id, 'roll_number' => 'DIT-081-04'],
                [
                    'user_id' => $studentFourUser->id,
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'registration_number' => 'MMP-DIT-081-004',
                    'current_semester' => 5,
                    'section' => 'A',
                    'batch' => '2081',
                    'admission_date' => now()->subMonths(1)->toDateString(),
                    'guardian_name' => 'Raju Rai',
                    'guardian_phone' => '9841000004',
                    'blood_group' => 'AB+',
                    'status' => 'active',
                    'is_archived' => false,
                ]
            );
            $this->restoreIfTrashed($studentFour);

            $parent = ParentModel::query()->updateOrCreate(
                ['user_id' => $parentUser->id],
                [
                    'occupation' => 'Business',
                ]
            );
            $parent->children()->syncWithoutDetaching([$studentOne->id, $studentTwo->id]);

            $parentTwoUser = $this->seedUser('Rupa Shrestha', 'parent02@mmp.edu.np', 'parent');
            $parentTwo = ParentModel::query()->updateOrCreate(
                ['user_id' => $parentTwoUser->id],
                [
                    'occupation' => 'Teacher',
                ]
            );
            $parentTwo->children()->syncWithoutDetaching([$studentThree->id, $studentFour->id]);

            $coreStudents = [$studentOne, $studentTwo, $studentThree, $studentFour];

            $alumni = Alumni::withTrashed()->updateOrCreate(
                ['user_id' => $alumniUser->id],
                [
                    'student_id' => null,
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                    'graduation_year' => '2080',
                    'current_job' => 'Junior Developer',
                    'company_name' => 'MMP Labs',
                    'achievements' => 'Placed through campus recruitment and active in alumni mentoring.',
                    'is_featured' => true,
                    'is_verified' => true,
                ]
            );
            $this->restoreIfTrashed($alumni);

            $teacher->subjects()->syncWithoutDetaching([
                $subjects['CG501']->id => ['academic_session_id' => $session->id, 'section' => 'A'],
                $subjects['WT502']->id => ['academic_session_id' => $session->id, 'section' => 'A'],
                $subjects['DBMS503']->id => ['academic_session_id' => $session->id, 'section' => 'A'],
            ]);

            SiteSetting::ensureDefaults();
            $this->seedSiteSettingFiles($assets);

            $this->seedExecutives($assets);
            $this->seedFacilities($department, $program, $assets);
            $this->seedStaff($assets);
            $this->seedPages($assets);
            $this->seedBanners($assets);
            $this->seedDownloads($principal, $department, $assets);

            $notices = $this->seedNotices($principal, $department, $program, $assets);
            $this->seedNoticeAttachments($notices, $assets);
            $this->seedMedia($principal, $department, $assets);
            $this->seedCommunications($principal, $hod, $teacherUser, $studentOneUser, $parentUser, $alumniUser);

            $this->seedTimetableAndAttendance($session, $program, $teacher, $subjects['CG501'], $subjects['WT502'], $subjects['DBMS503'], $coreStudents);
            $exam = $this->seedExam($session, $department, $program);
            $this->seedMarks($exam, $teacher, $coreStudents, $subjects['CG501'], $subjects['WT502'], $subjects['DBMS503']);
            $this->seedAssignments($teacher, $program, $subjects['WT502'], $studentOne, $studentTwo, $assets);
            $this->seedAuditLog($principal, $department, $program, $exam);
        });

        PublicDataService::invalidate('*');
        Cache::forget('brand:site_logo');

        if ($this->command) {
            $this->command->info('Demo data seeded across academic, CMS, and operations tables.');
            $this->command->info('Demo login password for seeded users: password');
        }
    }

    public function seedAssets(): array
    {
        $paths = [
            'site_logo' => 'seeded/site/logo.png',
            'principal_photo' => 'seeded/site/principal-photo.png',
            'principal_message_media' => 'seeded/site/principal-message-media.png',
            'executive_principal' => 'seeded/executives/principal.png',
            'executive_president' => 'seeded/executives/president.png',
            'facility_lab' => 'seeded/facilities/lab.png',
            'facility_library' => 'seeded/facilities/library.png',
            'staff_accountant' => 'seeded/staff/accountant.png',
            'staff_librarian' => 'seeded/staff/librarian.png',
            'page_about' => 'seeded/pages/about.png',
            'page_contact' => 'seeded/pages/contact.png',
            'page_scholarship' => 'seeded/pages/scholarship.png',
            'page_internship' => 'seeded/pages/internship.png',
            'banner_one' => 'seeded/banners/banner-1.png',
            'banner_two' => 'seeded/banners/banner-2.png',
            'download_prospectus' => 'seeded/downloads/prospectus.png',
            'download_form' => 'seeded/downloads/admission-form.png',
            'download_syllabus' => 'seeded/downloads/syllabus.png',
            'download_notes' => 'seeded/downloads/notes.png',
            'download_question_bank' => 'seeded/downloads/question-bank.png',
            'download_reports' => 'seeded/downloads/reports.png',
            'notice_general' => 'seeded/notices/general.png',
            'notice_exam' => 'seeded/notices/exam.png',
            'notice_news' => 'seeded/notices/news.png',
            'notice_event' => 'seeded/notices/event.png',
            'notice_attachment' => 'seeded/notices/attachment.png',
            'media_one' => 'seeded/media/gallery-1.png',
            'media_two' => 'seeded/media/gallery-2.png',
            'media_three' => 'seeded/media/gallery-3.png',
            'assignment_attachment' => 'seeded/assignments/assignment.png',
        ];

        $assets = [];
        foreach ($paths as $key => $path) {
            $assets[$key] = $this->copyPlaceholderImage($path);
        }

        return $assets;
    }

    public function copyPlaceholderImage(string $path): string
    {
        $source = public_path('assets/image.png');
        if (! is_file($source)) {
            throw new RuntimeException('Missing placeholder asset: ' . $source);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            $content = file_get_contents($source);
            if ($content === false) {
                throw new RuntimeException('Failed to read placeholder asset: ' . $source);
            }
            $disk->put($path, $content);
        }

        return $path;
    }
    public function seedUser(string $name, string $email, string $role): User
    {
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $this->restoreIfTrashed($user);
        $user->syncRoles([$role]);

        return $user;
    }

    public function restoreIfTrashed($model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }
    }

    public function seedSiteSettingFiles(array $assets): void
    {
        $mapping = [
            'site_logo' => $assets['site_logo'],
            'principal_photo' => $assets['principal_photo'],
            'principal_message_media' => $assets['principal_message_media'],
        ];

        foreach ($mapping as $key => $path) {
            SiteSetting::query()
                ->where('key', $key)
                ->where(function ($query) {
                    $query->whereNull('value')->orWhere('value', '');
                })
                ->update(['value' => $path]);
        }
    }

    public function seedExecutives(array $assets): void
    {
        $items = [
            [
                'name' => 'Dr. Principal',
                'type' => 'principal',
                'designation' => 'Principal',
                'start_date_bs' => '2081-01-01',
                'end_date_bs' => null,
                'is_current' => true,
                'avatar' => $assets['executive_principal'],
                'message' => 'Committed to hands-on technical education and student success.',
                'order' => 1,
            ],
            [
                'name' => 'Mr. Surya Bahadur Shrestha',
                'type' => 'president',
                'designation' => 'President',
                'start_date_bs' => '2080-01-01',
                'end_date_bs' => null,
                'is_current' => true,
                'avatar' => $assets['executive_president'],
                'message' => 'Guiding the institution with a focus on quality, discipline, and growth.',
                'order' => 0,
            ],
        ];

        foreach ($items as $item) {
            Executive::query()->updateOrCreate(
                ['name' => $item['name'], 'type' => $item['type']],
                $item
            );
        }
    }

    public function seedFacilities(Department $department, Program $program, array $assets): void
    {
        $items = [
            [
                'name' => 'Computer Lab',
                'category' => 'lab',
                'department_id' => $department->id,
                'program_id' => $program->id,
                'description' => 'Modern computer lab for practical sessions and lab work.',
                'content' => 'The computer lab is equipped for programming, networking, and multimedia practice.',
                'images' => [$assets['facility_lab']],
                'documents' => [],
                'videos' => [],
                'capacity' => 40,
                'location' => 'Block B, Room 204',
                'is_published' => true,
            ],
            [
                'name' => 'Library',
                'category' => 'library',
                'department_id' => null,
                'program_id' => null,
                'description' => 'Quiet study space with reference materials and books.',
                'content' => 'The library supports reading, research, and self-study for all programs.',
                'images' => [$assets['facility_library']],
                'documents' => [],
                'videos' => [],
                'capacity' => 60,
                'location' => 'Ground Floor',
                'is_published' => true,
            ],
        ];

        foreach ($items as $item) {
            Facility::query()->updateOrCreate(['name' => $item['name']], $item);
        }
    }

    public function seedStaff(array $assets): void
    {
        $items = [
            [
                'staff_code' => 'STA-001',
                'name' => 'Suresh Adhikari',
                'designation' => 'Accountant',
                'department' => 'Administration',
                'email' => 'accountant@mmp.edu.np',
                'phone' => '9841000101',
                'address' => 'Biratnagar, Morang',
                'dob' => '1986-02-14',
                'gender' => 'male',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'join_date' => now()->subYears(6)->toDateString(),
                'salary_amount' => 45000,
                'working_schedule' => [
                    'label' => 'Office Hours',
                    'days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                    'start' => '09:00',
                    'end' => '17:00',
                ],
                'assigned_roles' => ['Finance', 'Budgeting', 'Audit Support'],
                'responsibilities' => ['Fee reconciliation', 'Payroll support', 'Financial reports'],
                'bio' => 'Manages billing, ledgers, payroll support, and audit coordination for the college office.',
                'public_visible' => true,
                'featured' => true,
                'show_email_public' => true,
                'show_phone_public' => true,
                'photo' => $assets['staff_accountant'],
                'order' => 1,
                'is_active' => true,
                'documents' => [
                    [
                        'document_type' => 'appointment_letter',
                        'label' => 'Appointment Letter',
                        'file_path' => $assets['staff_accountant'],
                        'issued_at' => now()->subYears(6)->toDateString(),
                        'is_public' => true,
                        'notes' => 'Seeded public staff document.',
                    ],
                ],
                'attendance' => [
                    ['attendance_date' => now()->subDays(3)->toDateString(), 'status' => 'present', 'check_in' => '09:03', 'check_out' => '17:04'],
                    ['attendance_date' => now()->subDays(2)->toDateString(), 'status' => 'present', 'check_in' => '09:00', 'check_out' => '17:02'],
                    ['attendance_date' => now()->subDay()->toDateString(), 'status' => 'late', 'check_in' => '09:18', 'check_out' => '17:05'],
                ],
            ],
            [
                'staff_code' => 'STA-002',
                'name' => 'Laxmi Shrestha',
                'designation' => 'Librarian',
                'department' => 'Library',
                'email' => 'librarian@mmp.edu.np',
                'phone' => '9841000102',
                'address' => 'Biratnagar, Morang',
                'dob' => '1988-08-19',
                'gender' => 'female',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'join_date' => now()->subYears(5)->toDateString(),
                'salary_amount' => 39000,
                'working_schedule' => [
                    'label' => 'Library Hours',
                    'days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                    'start' => '09:30',
                    'end' => '16:30',
                ],
                'assigned_roles' => ['Cataloguing', 'Reader Support'],
                'responsibilities' => ['Book circulation', 'Library coordination', 'Digital catalog maintenance'],
                'bio' => 'Coordinates book circulation, cataloguing, and reader support for students and staff.',
                'public_visible' => true,
                'featured' => true,
                'show_email_public' => true,
                'show_phone_public' => false,
                'photo' => $assets['staff_librarian'],
                'order' => 2,
                'is_active' => true,
                'documents' => [
                    [
                        'document_type' => 'certification',
                        'label' => 'Library Science Certification',
                        'file_path' => $assets['staff_librarian'],
                        'issued_at' => now()->subYears(4)->toDateString(),
                        'is_public' => true,
                        'notes' => 'Seeded public staff document.',
                    ],
                ],
                'attendance' => [
                    ['attendance_date' => now()->subDays(3)->toDateString(), 'status' => 'present', 'check_in' => '09:12', 'check_out' => '16:28'],
                    ['attendance_date' => now()->subDays(2)->toDateString(), 'status' => 'leave', 'check_in' => null, 'check_out' => null],
                    ['attendance_date' => now()->subDay()->toDateString(), 'status' => 'present', 'check_in' => '09:09', 'check_out' => '16:31'],
                ],
            ],
            [
                'staff_code' => 'STA-003',
                'name' => 'Bipin Karki',
                'designation' => 'Office Assistant',
                'department' => 'Administration',
                'email' => 'assistant@mmp.edu.np',
                'phone' => '9841000103',
                'address' => 'Biratnagar, Morang',
                'dob' => '1991-11-02',
                'gender' => 'male',
                'employment_type' => 'part_time',
                'employment_status' => 'active',
                'join_date' => now()->subYears(3)->toDateString(),
                'salary_amount' => 28000,
                'working_schedule' => [
                    'label' => 'Morning Shift',
                    'days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                    'start' => '08:30',
                    'end' => '14:30',
                ],
                'assigned_roles' => ['Reception', 'Records', 'Visitor Support'],
                'responsibilities' => ['File organization', 'Visitor guidance', 'General office support'],
                'bio' => 'Supports daily office operations, record handling, and visitor coordination.',
                'public_visible' => true,
                'featured' => false,
                'show_email_public' => true,
                'show_phone_public' => false,
                'photo' => null,
                'order' => 3,
                'is_active' => true,
                'documents' => [
                    [
                        'document_type' => 'id_card',
                        'label' => 'Staff ID Card',
                        'file_path' => $assets['staff_accountant'],
                        'issued_at' => now()->subYears(3)->toDateString(),
                        'is_public' => false,
                        'notes' => 'Private identifier record.',
                    ],
                ],
                'attendance' => [
                    ['attendance_date' => now()->subDays(3)->toDateString(), 'status' => 'present', 'check_in' => '08:40', 'check_out' => '14:32'],
                    ['attendance_date' => now()->subDays(2)->toDateString(), 'status' => 'present', 'check_in' => '08:35', 'check_out' => '14:28'],
                    ['attendance_date' => now()->subDay()->toDateString(), 'status' => 'present', 'check_in' => '08:42', 'check_out' => '14:30'],
                ],
            ],
            [
                'staff_code' => 'STA-004',
                'name' => 'Rupa Neupane',
                'designation' => 'Procurement Officer',
                'department' => 'Procurement',
                'email' => 'procurement@mmp.edu.np',
                'phone' => '9841000104',
                'address' => 'Biratnagar, Morang',
                'dob' => '1989-05-24',
                'gender' => 'female',
                'employment_type' => 'contract',
                'employment_status' => 'leave',
                'join_date' => now()->subYears(2)->toDateString(),
                'end_date' => null,
                'salary_amount' => 41000,
                'working_schedule' => [
                    'label' => 'Procurement Window',
                    'days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday'],
                    'start' => '10:00',
                    'end' => '16:00',
                ],
                'assigned_roles' => ['Purchasing', 'Vendor Coordination'],
                'responsibilities' => ['Purchase approvals', 'Vendor coordination', 'Stock procurement'],
                'bio' => 'Coordinates purchasing workflows and vendor communication for the institution.',
                'public_visible' => false,
                'featured' => false,
                'show_email_public' => false,
                'show_phone_public' => false,
                'photo' => null,
                'order' => 4,
                'is_active' => true,
                'documents' => [],
                'attendance' => [
                    ['attendance_date' => now()->subDays(3)->toDateString(), 'status' => 'leave', 'check_in' => null, 'check_out' => null],
                    ['attendance_date' => now()->subDays(2)->toDateString(), 'status' => 'leave', 'check_in' => null, 'check_out' => null],
                    ['attendance_date' => now()->subDay()->toDateString(), 'status' => 'absent', 'check_in' => null, 'check_out' => null],
                ],
            ],
        ];

        foreach ($items as $item) {
            $documents = $item['documents'] ?? [];
            $attendanceRecords = $item['attendance'] ?? [];

            unset($item['documents'], $item['attendance']);

            $staff = Staff::query()->updateOrCreate(['email' => $item['email']], $item);

            foreach ($documents as $document) {
                $fileSize = Storage::disk('public')->exists($document['file_path'])
                    ? Storage::disk('public')->size($document['file_path'])
                    : null;

                StaffDocument::query()->updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'label' => $document['label'],
                    ],
                    [
                        'document_type' => $document['document_type'],
                        'file_path' => $document['file_path'],
                        'mime_type' => Storage::disk('public')->exists($document['file_path']) ? Storage::disk('public')->mimeType($document['file_path']) : null,
                        'file_size' => $fileSize,
                        'issued_at' => $document['issued_at'] ?? null,
                        'is_public' => $document['is_public'] ?? false,
                        'notes' => $document['notes'] ?? null,
                    ]
                );
            }

            foreach ($attendanceRecords as $attendance) {
                StaffAttendance::query()->updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'attendance_date' => $attendance['attendance_date'],
                    ],
                    [
                        'status' => $attendance['status'] ?? 'present',
                        'check_in' => $attendance['check_in'] ?? null,
                        'check_out' => $attendance['check_out'] ?? null,
                        'notes' => $attendance['notes'] ?? null,
                    ]
                );
            }
        }
    }

    public function seedPages(array $assets): void
    {
        $items = [
            [
                'title' => 'What is MMP',
                'slug' => 'what-is-mmp',
                'content' => 'Manmohan Memorial Polytechnic is a technical institute focused on practical education, career readiness, and disciplined learning.',
                'featured_image' => $assets['page_about'],
                'meta_title' => 'What is MMP',
                'meta_description' => 'Learn about Manmohan Memorial Polytechnic and its institutional identity.',
                'is_published' => true,
            ],
            [
                'title' => 'Objectives',
                'slug' => 'objectives',
                'content' => 'Our objectives are to develop competent graduates, strengthen technical skills, and prepare students for employment and entrepreneurship.',
                'featured_image' => $assets['page_about'],
                'meta_title' => 'Objectives',
                'meta_description' => 'Read the institutional objectives of Manmohan Memorial Polytechnic.',
                'is_published' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => 'Reach out to us for admissions, academic information, facility visits, and institutional support.',
                'featured_image' => $assets['page_contact'],
                'meta_title' => 'Contact Us',
                'meta_description' => 'Get in touch with Manmohan Memorial Polytechnic.',
                'is_published' => true,
            ],
            [
                'title' => 'Scholarship Schemes',
                'slug' => 'scholarship-schemes',
                'content' => 'Merit-based and need-based scholarship support is available to eligible students.',
                'featured_image' => $assets['page_scholarship'],
                'meta_title' => 'Scholarship Schemes',
                'meta_description' => 'Explore scholarship schemes available at Manmohan Memorial Polytechnic.',
                'is_published' => true,
            ],
            [
                'title' => 'Internships & Placements',
                'slug' => 'internships',
                'content' => 'Students are encouraged to complete practical internships and placement-ready project work before graduation.',
                'featured_image' => $assets['page_internship'],
                'meta_title' => 'Internships & Placements',
                'meta_description' => 'Learn about internship and placement opportunities at MMP.',
                'is_published' => true,
            ],
        ];

        foreach ($items as $item) {
            Page::query()->updateOrCreate(['slug' => $item['slug']], $item);
        }
    }

    public function seedBanners(array $assets): void
    {
        $items = [
            [
                'title' => 'Admissions Open for 2081',
                'subtitle' => 'Diploma programs focused on practical learning and job readiness.',
                'image' => $assets['banner_one'],
                'link' => '/admissions',
                'order' => 1,
                'is_active' => true,
                'button_text' => 'Apply Now',
                'button_link' => '/admissions',
            ],
            [
                'title' => 'Hands-on Learning for Future Engineers',
                'subtitle' => 'Laboratories, workshops, and project-based instruction.',
                'image' => $assets['banner_two'],
                'link' => '/programs',
                'order' => 2,
                'is_active' => true,
                'button_text' => 'Explore Programs',
                'button_link' => '/programs',
            ],
        ];

        foreach ($items as $item) {
            Banner::query()->updateOrCreate(['title' => $item['title']], $item);
        }
    }

    public function seedDownloads(User $principal, Department $department, array $assets): void
    {
        $items = [
            [
                'title' => 'MMP Prospectus 2081',
                'file_path' => $assets['download_prospectus'],
                'file_name' => 'MMP Prospectus 2081.png',
                'file_type' => 'png',
                'file_size' => Storage::disk('public')->size($assets['download_prospectus']),
                'description' => 'General prospectus for applicants.',
                'category' => 'Admissions',
                'department_id' => $department->id,
                'is_public' => true,
                'uploaded_by' => $principal->id,
            ],
            [
                'title' => 'Admission Form',
                'file_path' => $assets['download_form'],
                'file_name' => 'Admission Form.png',
                'file_type' => 'png',
                'file_size' => Storage::disk('public')->size($assets['download_form']),
                'description' => 'Printable admission form for new applicants.',
                'category' => 'Forms',
                'department_id' => null,
                'is_public' => true,
                'uploaded_by' => $principal->id,
            ],
            [
                'title' => 'Diploma in IT Syllabus',
                'file_path' => $assets['download_syllabus'],
                'file_name' => 'Diploma in IT Syllabus.png',
                'file_type' => 'png',
                'file_size' => Storage::disk('public')->size($assets['download_syllabus']),
                'description' => 'Program syllabus for the Diploma in Information Technology.',
                'category' => 'Syllabus',
                'department_id' => $department->id,
                'is_public' => true,
                'uploaded_by' => $principal->id,
            ],
            [
                'title' => 'Web Technology I Notes',
                'file_path' => $assets['download_notes'],
                'file_name' => 'Web Technology I Notes.png',
                'file_type' => 'png',
                'file_size' => Storage::disk('public')->size($assets['download_notes']),
                'description' => 'Class notes and handouts for Web Technology I.',
                'category' => 'Notes',
                'department_id' => $department->id,
                'is_public' => true,
                'uploaded_by' => $principal->id,
            ],
            [
                'title' => 'Computer Graphics Question Bank',
                'file_path' => $assets['download_question_bank'],
                'file_name' => 'Computer Graphics Question Bank.png',
                'file_type' => 'png',
                'file_size' => Storage::disk('public')->size($assets['download_question_bank']),
                'description' => 'Model question bank for Computer Graphics.',
                'category' => 'Question Bank',
                'department_id' => $department->id,
                'is_public' => true,
                'uploaded_by' => $principal->id,
            ],
            [
                'title' => 'MMP Annual Report 2081',
                'file_path' => $assets['download_reports'],
                'file_name' => 'MMP Annual Report 2081.png',
                'file_type' => 'png',
                'file_size' => Storage::disk('public')->size($assets['download_reports']),
                'description' => 'Annual report and publication archive.',
                'category' => 'Reports',
                'department_id' => null,
                'is_public' => true,
                'uploaded_by' => $principal->id,
            ],
        ];

        foreach ($items as $item) {
            Download::query()->updateOrCreate(['title' => $item['title']], $item);
        }
    }

    public function seedNotices(User $principal, Department $department, Program $program, array $assets): array
    {
        $items = [
            [
                'title' => 'Admission Open for 2081',
                'slug' => 'admission-open-for-2081',
                'content' => 'Applications are open for the new academic session. Interested students may contact the administration office for details.',
                'attachment' => $assets['notice_general'],
                'type' => 'general',
                'department_id' => null,
                'program_id' => null,
                'semester' => null,
                'created_by' => $principal->id,
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Mid Term Examination Schedule',
                'slug' => 'mid-term-examination-schedule',
                'content' => 'The mid-term examination schedule for semester five has been published. Please check the timetable section for details.',
                'attachment' => $assets['notice_exam'],
                'type' => 'exam',
                'department_id' => $department->id,
                'program_id' => $program->id,
                'semester' => 5,
                'created_by' => $principal->id,
                'is_published' => true,
                'published_at' => now()->subDay(),
            ],
            [
                'title' => 'Campus News: New Computer Lab Equipment Installed',
                'slug' => 'campus-news-new-computer-lab-equipment-installed',
                'content' => 'The IT department has upgraded the computer lab with new workstations, networking equipment, and modern presentation tools.',
                'attachment' => $assets['notice_news'],
                'type' => 'news',
                'department_id' => $department->id,
                'program_id' => $program->id,
                'semester' => null,
                'created_by' => $principal->id,
                'is_published' => true,
                'published_at' => now()->subHours(18),
            ],
            [
                'title' => 'Annual Sports Meet 2081',
                'slug' => 'annual-sports-meet-2081',
                'content' => 'Students and staff are invited to participate in the annual sports meet and cultural activities scheduled for this month.',
                'attachment' => $assets['notice_event'],
                'type' => 'event',
                'department_id' => null,
                'program_id' => null,
                'semester' => null,
                'created_by' => $principal->id,
                'is_published' => true,
                'published_at' => now()->subHours(6),
            ],
        ];

        $notices = [];
        foreach ($items as $item) {
            $notice = Notice::withTrashed()->updateOrCreate(['slug' => $item['slug']], $item);
            $this->restoreIfTrashed($notice);
            $notices[] = $notice;
        }

        return $notices;
    }

    public function seedNoticeAttachments(array $notices, array $assets): void
    {
        $attachments = [
            [
                'notice' => $notices[0],
                'file_path' => $assets['notice_general'],
                'file_name' => 'Admission Open 2081.png',
                'file_type' => 'png',
            ],
            [
                'notice' => $notices[1],
                'file_path' => $assets['notice_exam'],
                'file_name' => 'Mid Term Examination Schedule.png',
                'file_type' => 'png',
            ],
            [
                'notice' => $notices[2],
                'file_path' => $assets['notice_news'],
                'file_name' => 'Campus News Update.png',
                'file_type' => 'png',
            ],
            [
                'notice' => $notices[3],
                'file_path' => $assets['notice_event'],
                'file_name' => 'Annual Sports Meet 2081.png',
                'file_type' => 'png',
            ],
        ];

        foreach ($attachments as $item) {
            NoticeAttachment::query()->updateOrCreate(
                [
                    'notice_id' => $item['notice']->id,
                    'file_path' => $item['file_path'],
                ],
                [
                    'file_name' => $item['file_name'],
                    'file_type' => $item['file_type'],
                    'file_size' => Storage::disk('public')->size($item['file_path']),
                ]
            );
        }
    }

    public function seedMedia(User $principal, Department $department, array $assets): void
    {
        $items = [
            [
                'title' => 'Campus Entrance',
                'file_path' => $assets['media_one'],
                'file_type' => 'gallery',
                'mime_type' => 'image/png',
                'size' => Storage::disk('public')->size($assets['media_one']),
                'department_id' => null,
                'uploaded_by' => $principal->id,
            ],
            [
                'title' => 'Computer Lab Session',
                'file_path' => $assets['media_two'],
                'file_type' => 'gallery',
                'mime_type' => 'image/png',
                'size' => Storage::disk('public')->size($assets['media_two']),
                'department_id' => $department->id,
                'uploaded_by' => $principal->id,
            ],
            [
                'title' => 'Workshop Practice',
                'file_path' => $assets['media_three'],
                'file_type' => 'gallery',
                'mime_type' => 'image/png',
                'size' => Storage::disk('public')->size($assets['media_three']),
                'department_id' => $department->id,
                'uploaded_by' => $principal->id,
            ],
        ];

        foreach ($items as $item) {
            Media::query()->updateOrCreate(['file_path' => $item['file_path']], $item);
        }
    }

    public function seedCommunications(User $principal, User $hod, User $teacher, User $student, User $parent, User $alumni): void
    {
        $items = [
            [
                'sender_id' => $principal->id,
                'receiver_id' => $hod->id,
                'subject' => 'Welcome to the new session',
                'message' => 'Please review the seeded content and verify the admin workflows.',
                'is_read' => false,
            ],
            [
                'sender_id' => $hod->id,
                'receiver_id' => $teacher->id,
                'subject' => 'Semester five schedule',
                'message' => 'Please begin classes according to the seeded timetable.',
                'is_read' => true,
            ],
            [
                'sender_id' => $teacher->id,
                'receiver_id' => $student->id,
                'subject' => 'Assignment reminder',
                'message' => 'Submit your assignment before Friday.',
                'is_read' => false,
            ],
            [
                'sender_id' => $parent->id,
                'receiver_id' => $principal->id,
                'subject' => 'Query about progress',
                'message' => 'Please share the current academic progress for the seeded demo accounts.',
                'is_read' => false,
            ],
            [
                'sender_id' => $alumni->id,
                'receiver_id' => $principal->id,
                'subject' => 'Alumni mentoring',
                'message' => 'Happy to support the next generation of students.',
                'is_read' => true,
            ],
        ];

        foreach ($items as $item) {
            Communication::query()->updateOrCreate(
                [
                    'sender_id' => $item['sender_id'],
                    'receiver_id' => $item['receiver_id'],
                    'subject' => $item['subject'],
                ],
                $item
            );
        }
    }

    public function seedDepartmentProfile(array $profile, AcademicSession $session, array $assets): void
    {
        $hodUser = $this->seedUser($profile['hod_name'], $profile['hod_email'], 'hod');

        $department = Department::withTrashed()->updateOrCreate(
            ['code' => $profile['department_code']],
            [
                'name' => $profile['department_name'],
                'slug' => Str::slug($profile['department_name']),
                'description' => $profile['description'],
                'photo' => null,
                'syllabus' => null,
                'seat_capacity' => 40,
                'hod_id' => $hodUser->id,
                'is_active' => true,
            ]
        );
        $this->restoreIfTrashed($department);

        $program = Program::withTrashed()->updateOrCreate(
            ['code' => $profile['program_code']],
            [
                'department_id' => $department->id,
                'name' => $profile['program_name'],
                'slug' => Str::slug($profile['program_name']),
                'total_semesters' => 6,
                'duration_years' => 3,
                'description' => $profile['description'],
                'is_active' => true,
            ]
        );
        $this->restoreIfTrashed($program);

        $teacherUser = $this->seedUser($profile['teacher_name'], $profile['teacher_email'], 'teacher');
        $teacher = Teacher::withTrashed()->updateOrCreate(
            ['employee_id' => $profile['teacher_employee_id']],
            [
                'user_id' => $teacherUser->id,
                'department_id' => $department->id,
                'designation' => $profile['teacher_designation'],
                'qualification' => $profile['teacher_qualification'],
                'specialization' => $profile['teacher_specialization'],
                'join_date' => now()->subYears(2)->toDateString(),
                'employment_type' => 'permanent',
                'is_active' => true,
            ]
        );
        $this->restoreIfTrashed($teacher);

        $studentUser = $this->seedUser($profile['student_name'], $profile['student_email'], 'student');
        $student = Student::withTrashed()->updateOrCreate(
            ['academic_session_id' => $session->id, 'roll_number' => $profile['student_roll_number']],
            [
                'user_id' => $studentUser->id,
                'department_id' => $department->id,
                'program_id' => $program->id,
                'registration_number' => $profile['student_registration_number'],
                'current_semester' => $profile['student_current_semester'],
                'section' => $profile['student_section'],
                'batch' => $profile['student_batch'],
                'admission_date' => now()->subMonths(1)->toDateString(),
                'guardian_name' => $profile['guardian_name'],
                'guardian_phone' => $profile['guardian_phone'],
                'blood_group' => $profile['blood_group'],
                'status' => 'active',
                'is_archived' => false,
            ]
        );
        $this->restoreIfTrashed($student);

        $parentUser = $this->seedUser($profile['parent_name'], $profile['parent_email'], 'parent');
        $parent = ParentModel::query()->updateOrCreate(
            ['user_id' => $parentUser->id],
            [
                'occupation' => $profile['parent_occupation'],
            ]
        );
        $parent->children()->syncWithoutDetaching([$student->id]);

        $alumniUser = $this->seedUser($profile['alumni_name'], $profile['alumni_email'], 'alumni');
        $alumni = Alumni::withTrashed()->updateOrCreate(
            ['user_id' => $alumniUser->id],
            [
                'student_id' => $student->id,
                'department_id' => $department->id,
                'program_id' => $program->id,
                'graduation_year' => $profile['graduation_year'],
                'current_job' => $profile['current_job'],
                'company_name' => $profile['company_name'],
                'achievements' => $profile['achievements'],
                'is_featured' => true,
                'is_verified' => true,
            ]
        );
        $this->restoreIfTrashed($alumni);

        $notice = Notice::withTrashed()->updateOrCreate(
            ['slug' => $profile['notice_slug']],
            [
                'title' => $profile['notice_title'],
                'content' => $profile['notice_content'],
                'attachment' => $assets['notice_attachment'],
                'type' => 'department',
                'department_id' => $department->id,
                'program_id' => $program->id,
                'semester' => $profile['student_current_semester'],
                'created_by' => $hodUser->id,
                'is_published' => true,
                'published_at' => now()->subHours(12),
            ]
        );
        $this->restoreIfTrashed($notice);

        NoticeAttachment::query()->updateOrCreate(
            [
                'notice_id' => $notice->id,
                'file_path' => $assets['notice_attachment'],
            ],
            [
                'file_name' => $profile['notice_title'] . '.png',
                'file_type' => 'png',
                'file_size' => Storage::disk('public')->size($assets['notice_attachment']),
            ]
        );
    }

    public function seedTimetableAndAttendance(AcademicSession $session, Program $program, Teacher $teacher, Subject $subjectOne, Subject $subjectTwo, Subject $subjectThree, array $students): void
    {
        $timetable = Timetable::query()->updateOrCreate(
            [
                'academic_session_id' => $session->id,
                'program_id' => $program->id,
                'semester' => 5,
                'section' => 'A',
            ],
            [
                'start_date' => $session->start_date,
                'effective_from' => now()->startOfWeek()->toDateString(),
                'is_active' => true,
            ]
        );

        $slots = [
            [
                'day_of_week' => 'monday',
                'start_time' => '10:00:00',
                'end_time' => '10:50:00',
                'subject_id' => $subjectOne->id,
                'teacher_id' => $teacher->id,
                'room_number' => 'Lab-1',
                'type' => 'lab',
                'group' => 'A',
            ],
            [
                'day_of_week' => 'wednesday',
                'start_time' => '11:00:00',
                'end_time' => '11:50:00',
                'subject_id' => $subjectTwo->id,
                'teacher_id' => $teacher->id,
                'room_number' => 'Room-204',
                'type' => 'theory',
                'group' => 'A',
            ],
            [
                'day_of_week' => 'friday',
                'start_time' => '12:00:00',
                'end_time' => '12:50:00',
                'subject_id' => $subjectThree->id,
                'teacher_id' => $teacher->id,
                'room_number' => 'Room-205',
                'type' => 'theory',
                'group' => 'A',
            ],
        ];

        foreach ($slots as $slot) {
            TimetableSlot::query()->updateOrCreate(
                [
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                    'subject_id' => $slot['subject_id'],
                    'teacher_id' => $slot['teacher_id'],
                ],
                $slot
            );
        }

        $attendancePlans = [
            [
                'subject' => $subjectOne,
                'date' => now()->subDay()->toDateString(),
                'period' => 'First Period',
                'statuses' => ['present', 'late', 'present', 'excused'],
            ],
            [
                'subject' => $subjectTwo,
                'date' => now()->subDays(2)->toDateString(),
                'period' => 'Second Period',
                'statuses' => ['present', 'present', 'absent', 'present'],
            ],
            [
                'subject' => $subjectThree,
                'date' => now()->subDays(3)->toDateString(),
                'period' => 'Third Period',
                'statuses' => ['present', 'present', 'late', 'present'],
            ],
        ];

        foreach ($attendancePlans as $plan) {
            $sessionRecord = AttendanceSession::query()->updateOrCreate(
                [
                    'academic_session_id' => $session->id,
                    'teacher_id' => $teacher->id,
                    'subject_id' => $plan['subject']->id,
                    'program_id' => $program->id,
                    'semester' => 5,
                    'section' => 'A',
                    'date' => $plan['date'],
                ],
                [
                    'period' => $plan['period'],
                ]
            );

            foreach ($students as $index => $student) {
                $status = $plan['statuses'][$index] ?? 'present';
                $remarks = match ($status) {
                    'late' => 'Arrived after the bell',
                    'absent' => 'Marked absent',
                    'excused' => 'Excused with prior notice',
                    default => 'On time',
                };

                Attendance::query()->updateOrCreate(
                    ['attendance_session_id' => $sessionRecord->id, 'student_id' => $student->id],
                    ['status' => $status, 'remarks' => $remarks]
                );
            }
        }
    }

    public function seedExam(AcademicSession $session, Department $department, Program $program): Exam
    {
        $exam = Exam::withTrashed()->updateOrCreate(
            ['name' => 'First Internal Assessment - 2081'],
            [
                'academic_session_id' => $session->id,
                'department_id' => $department->id,
                'type' => 'assessment',
                'start_date' => now()->subWeeks(3)->toDateString(),
                'end_date' => now()->subWeeks(2)->toDateString(),
                'status' => 'results_published',
            ]
        );
        $this->restoreIfTrashed($exam);

        $exam->programs()->syncWithoutDetaching([
            $program->id => ['semester' => 5],
        ]);

        return $exam;
    }

    public function seedMarks(Exam $exam, Teacher $teacher, array $students, Subject $subjectOne, Subject $subjectTwo, Subject $subjectThree): void
    {
        $subjects = [$subjectOne, $subjectTwo, $subjectThree];
        $remarksBySubject = [
            'Strong practical performance.',
            'Good understanding of concepts.',
            'Consistent progress.',
        ];

        foreach ($students as $studentIndex => $student) {
            foreach ($subjects as $subjectIndex => $subject) {
                $isAbsent = $studentIndex === 3 && $subjectIndex === 1;
                $isWithheld = $studentIndex === 2 && $subjectIndex === 2;

                $hasMarks = ! $isAbsent && ! $isWithheld;
                $internalTheoryMarks = $hasMarks ? 16 + $studentIndex + $subjectIndex : null;
                $externalTheoryMarks = $hasMarks ? 58 + ($studentIndex * 2) - ($subjectIndex * 3) : null;
                $internalPracticalMarks = $hasMarks && $subject->hasPractical() ? 24 + $studentIndex : null;
                $externalPracticalMarks = $hasMarks && $subject->hasPractical() ? 16 + $subjectIndex : null;

                Mark::query()->updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'program_id' => $subject->program_id,
                        'semester' => $subject->semester,
                        'teacher_id' => $teacher->id,
                        'internal_theory_marks' => $internalTheoryMarks,
                        'external_theory_marks' => $externalTheoryMarks,
                        'internal_practical_marks' => $internalPracticalMarks,
                        'external_practical_marks' => $externalPracticalMarks,
                        'is_absent' => $isAbsent,
                        'is_withheld' => $isWithheld,
                        'status' => 'published',
                        'remarks' => $isAbsent
                            ? 'Absent during evaluation.'
                            : ($isWithheld
                                ? 'Withheld pending review.'
                                : $remarksBySubject[$subjectIndex]),
                    ]
                );
            }
        }
    }

    public function seedAssignments(Teacher $teacher, Program $program, Subject $subject, Student $studentOne, Student $studentTwo, array $assets): void
    {
        $assignment = Assignment::query()->updateOrCreate(
            ['title' => 'Build a responsive homepage'],
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'program_id' => $program->id,
                'semester' => 5,
                'section' => 'A',
                'description' => 'Create a simple responsive landing page for the college website.',
                'attachment' => $assets['assignment_attachment'],
                'due_date' => now()->addDays(7)->toDateString(),
            ]
        );

        $submissions = [
            [
                'student' => $studentOne,
                'student_note' => 'Submitted the first draft for review.',
                'attachment' => $assets['assignment_attachment'],
                'status' => 'submitted',
                'marks_obtained' => null,
                'teacher_feedback' => null,
            ],
            [
                'student' => $studentTwo,
                'student_note' => 'Included responsive navigation and footer sections.',
                'attachment' => $assets['assignment_attachment'],
                'status' => 'graded',
                'marks_obtained' => 88,
                'teacher_feedback' => 'Good layout and clean structure.',
            ],
        ];

        foreach ($submissions as $submission) {
            AssignmentSubmission::query()->updateOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'student_id' => $submission['student']->id,
                ],
                [
                    'student_note' => $submission['student_note'],
                    'attachment' => $submission['attachment'],
                    'status' => $submission['status'],
                    'marks_obtained' => $submission['marks_obtained'],
                    'teacher_feedback' => $submission['teacher_feedback'],
                ]
            );
        }
    }

    public function seedAuditLog(User $principal, Department $department, Program $program, Exam $exam): void
    {
        AuditLog::query()->updateOrCreate(
            ['action' => 'seed_demo_data', 'model_type' => self::class, 'model_id' => null],
            [
                'user_id' => $principal->id,
                'old_values' => null,
                'new_values' => [
                    'department' => $department->code,
                    'program' => $program->code,
                    'exam' => $exam->name,
                    'note' => 'Seeded demo data across the application tables.',
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'DatabaseSeeder',
            ]
        );
    }
}
