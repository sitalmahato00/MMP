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

            $parent = ParentModel::query()->updateOrCreate(
                ['user_id' => $parentUser->id],
                [
                    'occupation' => 'Business',
                ]
            );
            $parent->children()->syncWithoutDetaching([$studentOne->id, $studentTwo->id]);

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

            $this->seedTimetableAndAttendance($session, $program, $teacher, $subjects['CG501'], $subjects['WT502'], $studentOne, $studentTwo);
            $exam = $this->seedExam($session, $department, $program);
            $this->seedMarks($exam, $teacher, $studentOne, $studentTwo, $subjects['CG501'], $subjects['WT502'], $subjects['DBMS503']);
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

    private function seedAssets(): array
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
            'notice_general' => 'seeded/notices/general.png',
            'notice_exam' => 'seeded/notices/exam.png',
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

    private function copyPlaceholderImage(string $path): string
    {
        $source = public_path('assets/image.png');
        if (! is_file($source)) {
            throw new RuntimeException('Missing placeholder asset: ' . $source);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            $disk->put($path, file_get_contents($source));
        }

        return $path;
    }

    private function seedUser(string $name, string $email, string $role): User
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

    private function restoreIfTrashed($model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }
    }

    private function seedSiteSettingFiles(array $assets): void
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

    private function seedExecutives(array $assets): void
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

    private function seedFacilities(Department $department, Program $program, array $assets): void
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

    private function seedStaff(array $assets): void
    {
        $items = [
            [
                'name' => 'Suresh Adhikari',
                'designation' => 'Accountant',
                'department' => 'Administration',
                'email' => 'accountant@mmp.edu.np',
                'phone' => '9841000101',
                'photo' => $assets['staff_accountant'],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Laxmi Shrestha',
                'designation' => 'Librarian',
                'department' => 'Library',
                'email' => 'librarian@mmp.edu.np',
                'phone' => '9841000102',
                'photo' => $assets['staff_librarian'],
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Staff::query()->updateOrCreate(['email' => $item['email']], $item);
        }
    }

    private function seedPages(array $assets): void
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

    private function seedBanners(array $assets): void
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

    private function seedDownloads(User $principal, Department $department, array $assets): void
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
        ];

        foreach ($items as $item) {
            Download::query()->updateOrCreate(['title' => $item['title']], $item);
        }
    }

    private function seedNotices(User $principal, Department $department, Program $program, array $assets): array
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
        ];

        $notices = [];
        foreach ($items as $item) {
            $notice = Notice::withTrashed()->updateOrCreate(['slug' => $item['slug']], $item);
            $this->restoreIfTrashed($notice);
            $notices[] = $notice;
        }

        return $notices;
    }

    private function seedNoticeAttachments(array $notices, array $assets): void
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

    private function seedMedia(User $principal, Department $department, array $assets): void
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

    private function seedCommunications(User $principal, User $hod, User $teacher, User $student, User $parent, User $alumni): void
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

    private function seedTimetableAndAttendance(AcademicSession $session, Program $program, Teacher $teacher, Subject $subjectOne, Subject $subjectTwo, Student $studentOne, Student $studentTwo): void
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

        $sessionRecord = AttendanceSession::query()->updateOrCreate(
            [
                'academic_session_id' => $session->id,
                'teacher_id' => $teacher->id,
                'subject_id' => $subjectOne->id,
                'program_id' => $program->id,
                'semester' => 5,
                'section' => 'A',
                'date' => now()->subDay()->toDateString(),
            ],
            [
                'period' => 'First Period',
            ]
        );

        Attendance::query()->updateOrCreate(
            ['attendance_session_id' => $sessionRecord->id, 'student_id' => $studentOne->id],
            ['status' => 'present', 'remarks' => 'On time']
        );

        Attendance::query()->updateOrCreate(
            ['attendance_session_id' => $sessionRecord->id, 'student_id' => $studentTwo->id],
            ['status' => 'late', 'remarks' => 'Arrived after the bell']
        );
    }

    private function seedExam(AcademicSession $session, Department $department, Program $program): Exam
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

    private function seedMarks(Exam $exam, Teacher $teacher, Student $studentOne, Student $studentTwo, Subject $subjectOne, Subject $subjectTwo, Subject $subjectThree): void
    {
        $items = [
            [
                'exam_id' => $exam->id,
                'student_id' => $studentOne->id,
                'subject_id' => $subjectOne->id,
                'teacher_id' => $teacher->id,
                'internal_theory_marks' => 18,
                'external_theory_marks' => 61,
                'internal_practical_marks' => 26,
                'external_practical_marks' => 18,
                'is_absent' => false,
                'is_withheld' => false,
                'status' => 'published',
                'remarks' => 'Strong practical performance.',
            ],
            [
                'exam_id' => $exam->id,
                'student_id' => $studentOne->id,
                'subject_id' => $subjectTwo->id,
                'teacher_id' => $teacher->id,
                'internal_theory_marks' => 17,
                'external_theory_marks' => 63,
                'internal_practical_marks' => null,
                'external_practical_marks' => null,
                'is_absent' => false,
                'is_withheld' => false,
                'status' => 'published',
                'remarks' => 'Good understanding of concepts.',
            ],
            [
                'exam_id' => $exam->id,
                'student_id' => $studentTwo->id,
                'subject_id' => $subjectThree->id,
                'teacher_id' => $teacher->id,
                'internal_theory_marks' => 16,
                'external_theory_marks' => 58,
                'internal_practical_marks' => null,
                'external_practical_marks' => null,
                'is_absent' => false,
                'is_withheld' => false,
                'status' => 'published',
                'remarks' => 'Consistent progress.',
            ],
        ];

        foreach ($items as $item) {
            Mark::query()->updateOrCreate(
                [
                    'exam_id' => $item['exam_id'],
                    'student_id' => $item['student_id'],
                    'subject_id' => $item['subject_id'],
                ],
                $item
            );
        }
    }

    private function seedAssignments(Teacher $teacher, Program $program, Subject $subject, Student $studentOne, Student $studentTwo, array $assets): void
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

    private function seedAuditLog(User $principal, Department $department, Program $program, Exam $exam): void
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
