<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

trait CollegeDemoBaseSeeder
{
    protected const DEPARTMENT_COUNT = 6;
    protected const SEMESTER_COUNT = 6;
    protected const SUBJECTS_PER_SEMESTER = 6;
    protected const STUDENTS_PER_SEMESTER = 50;
    protected const MONTHLY_ASSESSMENTS_PER_SEMESTER = 3;
    protected const CTEVT_FINALS_PER_SEMESTER = 1;

    protected function prepareForSeeding(): void
    {
        DB::disableQueryLog();

        foreach (['principal', 'hod', 'teacher', 'student', 'parent', 'alumni'] as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }

    protected function collegeSession(): AcademicSession
    {
        return AcademicSession::query()->updateOrCreate(
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
                'notes' => 'Complete college demo data for feature testing.',
            ]
        );
    }

    protected function departmentCatalog(): array
    {
        return [
            [
                'department_code' => 'IT',
                'department_name' => 'Information Technology',
                'program_code' => 'DIT',
                'program_name' => 'Diploma in Information Technology',
                'department_description' => 'Information systems, development, networking, and computing support.',
                'program_description' => 'A practical diploma for software, database, and web development pathways.',
                'hod_name' => 'Er. Yubraj Chaudhary',
                'hod_email' => 'hod.it@mmp.edu.np',
                'teacher_primary_name' => 'Er. Anil Khatri',
                'teacher_primary_email' => 'teacher.it.primary@mmp.edu.np',
                'teacher_secondary_name' => 'Er. Sabin Shrestha',
                'teacher_secondary_email' => 'teacher.it.secondary@mmp.edu.np',
                'themes' => [
                    'Programming Fundamentals',
                    'Database Systems',
                    'Web Technology',
                    'Networking',
                    'Software Engineering',
                    'Project Practice',
                ],
                'alumni_prefix' => 'DIT',
            ],
            [
                'department_code' => 'AR',
                'department_name' => 'Architecture Engineering',
                'program_code' => 'DAE',
                'program_name' => 'Diploma in Architecture Engineering',
                'department_description' => 'Architectural design, drafting, planning, and building technology.',
                'program_description' => 'A studio-focused diploma covering design, drafting, and construction basics.',
                'hod_name' => 'Ar. Sushma Maharjan',
                'hod_email' => 'hod.ar@mmp.edu.np',
                'teacher_primary_name' => 'Ar. Prabin Karki',
                'teacher_primary_email' => 'teacher.ar.primary@mmp.edu.np',
                'teacher_secondary_name' => 'Ar. Samiksha Koirala',
                'teacher_secondary_email' => 'teacher.ar.secondary@mmp.edu.np',
                'themes' => [
                    'Design Studio',
                    'Drafting Techniques',
                    'Building Materials',
                    'Construction Technology',
                    'Urban Planning',
                    'Portfolio Practice',
                ],
                'alumni_prefix' => 'DAE',
            ],
            [
                'department_code' => 'CE',
                'department_name' => 'Civil Engineering',
                'program_code' => 'DCE',
                'program_name' => 'Diploma in Civil Engineering',
                'department_description' => 'Infrastructure design, construction, surveying, and site supervision.',
                'program_description' => 'A practical diploma for construction, structures, roads, and surveying.',
                'hod_name' => 'Er. Rajesh Joshi',
                'hod_email' => 'hod.ce@mmp.edu.np',
                'teacher_primary_name' => 'Er. Nabin Khatri',
                'teacher_primary_email' => 'teacher.ce.primary@mmp.edu.np',
                'teacher_secondary_name' => 'Er. Sagar Thapa',
                'teacher_secondary_email' => 'teacher.ce.secondary@mmp.edu.np',
                'themes' => [
                    'Surveying',
                    'Building Materials',
                    'Strength of Materials',
                    'Hydraulics',
                    'Transportation Engineering',
                    'Project Practice',
                ],
                'alumni_prefix' => 'DCE',
            ],
            [
                'department_code' => 'EL',
                'department_name' => 'Electrical Engineering',
                'program_code' => 'DEL',
                'program_name' => 'Diploma in Electrical Engineering',
                'department_description' => 'Electrical systems, wiring, machines, power, and installations.',
                'program_description' => 'A diploma focused on electrical theory, machines, and power applications.',
                'hod_name' => 'Er. Sarita Gurung',
                'hod_email' => 'hod.el@mmp.edu.np',
                'teacher_primary_name' => 'Er. Suman Bista',
                'teacher_primary_email' => 'teacher.el.primary@mmp.edu.np',
                'teacher_secondary_name' => 'Er. Prakash Adhikari',
                'teacher_secondary_email' => 'teacher.el.secondary@mmp.edu.np',
                'themes' => [
                    'Electrical Fundamentals',
                    'Electrical Machines',
                    'Power Systems',
                    'Control Systems',
                    'Renewable Energy',
                    'Industrial Practice',
                ],
                'alumni_prefix' => 'DEL',
            ],
            [
                'department_code' => 'EE',
                'department_name' => 'Electronics Engineering',
                'program_code' => 'DEE',
                'program_name' => 'Diploma in Electronics Engineering',
                'department_description' => 'Electronics circuits, communication systems, embedded systems, and signals.',
                'program_description' => 'A diploma for electronics, communication, embedded systems, and IoT.',
                'hod_name' => 'Er. Pooja Adhikari',
                'hod_email' => 'hod.ee@mmp.edu.np',
                'teacher_primary_name' => 'Er. Bikash Dhungana',
                'teacher_primary_email' => 'teacher.ee.primary@mmp.edu.np',
                'teacher_secondary_name' => 'Er. Nisha KC',
                'teacher_secondary_email' => 'teacher.ee.secondary@mmp.edu.np',
                'themes' => [
                    'Digital Electronics',
                    'Communication Systems',
                    'Embedded Systems',
                    'Signal Processing',
                    'IoT Systems',
                    'Project Practice',
                ],
                'alumni_prefix' => 'DEE',
            ],
            [
                'department_code' => 'ME',
                'department_name' => 'Mechanical Engineering',
                'program_code' => 'DME',
                'program_name' => 'Diploma in Mechanical Engineering',
                'department_description' => 'Machine design, thermodynamics, manufacturing, and workshop practice.',
                'program_description' => 'A diploma that covers workshop practice, machines, and industrial systems.',
                'hod_name' => 'Er. Krishna Bhandari',
                'hod_email' => 'hod.me@mmp.edu.np',
                'teacher_primary_name' => 'Er. Rabindra Kafle',
                'teacher_primary_email' => 'teacher.me.primary@mmp.edu.np',
                'teacher_secondary_name' => 'Er. Bibek Poudel',
                'teacher_secondary_email' => 'teacher.me.secondary@mmp.edu.np',
                'themes' => [
                    'Workshop Technology',
                    'Thermodynamics',
                    'Machine Design',
                    'Manufacturing Processes',
                    'Automobile Systems',
                    'Industrial Training',
                ],
                'alumni_prefix' => 'DME',
            ],
        ];
    }

    protected function profileForDepartmentCode(string $departmentCode): array
    {
        foreach ($this->departmentCatalog() as $profile) {
            if ($profile['department_code'] === $departmentCode) {
                return $profile;
            }
        }

        abort(500, 'Missing college demo profile for department code ' . $departmentCode);
    }

    protected function seedUser(string $name, string $email, string $role, ?string $phone = null): User
    {
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $this->restoreIfTrashed($user);
        $user->syncRoles([$role]);

        return $user;
    }

    protected function restoreIfTrashed(object $model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }
    }

    protected function subjectDefinitions(array $profile, int $semester): array
    {
        $slotDefinitions = [
            ['label' => 'Theory', 'type' => 'theory', 'teacher_slot' => 'primary'],
            ['label' => 'Lab', 'type' => 'practical', 'teacher_slot' => 'secondary'],
            ['label' => 'Core', 'type' => 'theory', 'teacher_slot' => 'primary'],
            ['label' => 'Workshop', 'type' => 'theory', 'teacher_slot' => 'primary'],
            ['label' => 'Practical', 'type' => 'practical', 'teacher_slot' => 'secondary'],
            ['label' => 'Project', 'type' => 'both', 'teacher_slot' => 'primary'],
        ];

        $themes = array_slice($profile['themes'], 0, self::SUBJECTS_PER_SEMESTER);
        $definitions = [];

        foreach ($themes as $index => $theme) {
            $slot = $index + 1;
            $slotDefinition = $slotDefinitions[$index] ?? $slotDefinitions[array_key_last($slotDefinitions)];
            $definitions[] = [
                'slot' => $slot,
                'theme' => $theme,
                'label' => $slotDefinition['label'],
                'type' => $slotDefinition['type'],
                'teacher_slot' => $slotDefinition['teacher_slot'],
                'code_suffix' => str_pad((string) $slot, 2, '0', STR_PAD_LEFT),
                'semester' => $semester,
                'marks' => $this->marksForType($slotDefinition['type']),
            ];
        }

        return $definitions;
    }

    protected function marksForType(string $type): array
    {
        return match ($type) {
            'practical' => [
                'full_marks_internal_theory' => 0,
                'full_marks_external_theory' => 0,
                'pass_marks_internal_theory' => 0,
                'pass_marks_external_theory' => 0,
                'full_marks_internal_practical' => 30,
                'full_marks_external_practical' => 20,
                'pass_marks_internal_practical' => 15,
                'pass_marks_external_practical' => 10,
                'credit_hours' => 2,
            ],
            'both' => [
                'full_marks_internal_theory' => 20,
                'full_marks_external_theory' => 80,
                'pass_marks_internal_theory' => 8,
                'pass_marks_external_theory' => 32,
                'full_marks_internal_practical' => 30,
                'full_marks_external_practical' => 20,
                'pass_marks_internal_practical' => 15,
                'pass_marks_external_practical' => 10,
                'credit_hours' => 4,
            ],
            default => [
                'full_marks_internal_theory' => 20,
                'full_marks_external_theory' => 80,
                'pass_marks_internal_theory' => 8,
                'pass_marks_external_theory' => 32,
                'full_marks_internal_practical' => 0,
                'full_marks_external_practical' => 0,
                'pass_marks_internal_practical' => 0,
                'pass_marks_external_practical' => 0,
                'credit_hours' => 3,
            ],
        };
    }

    protected function teacherEmployeeId(string $departmentCode, string $slot): string
    {
        return 'T-' . $departmentCode . '-' . strtoupper($slot);
    }

    protected function studentNo(string $programCode, int $semester, int $studentIndex): string
    {
        return sprintf('%s-S%d-%02d', $programCode, $semester, $studentIndex);
    }

    protected function rollNumber(string $programCode, int $semester, int $studentIndex): string
    {
        return sprintf('%s-%d-%02d', $programCode, $semester, $studentIndex);
    }

    protected function studentEmail(string $programCode, int $semester, int $studentIndex): string
    {
        return strtolower(sprintf('student.%s.s%d.%02d@mmp.edu.np', $programCode, $semester, $studentIndex));
    }

    protected function parentEmail(string $programCode, int $semester, int $studentIndex): string
    {
        return strtolower(sprintf('parent.%s.s%d.%02d@mmp.edu.np', $programCode, $semester, $studentIndex));
    }

    protected function alumniEmail(string $programCode, int $alumniIndex): string
    {
        return strtolower(sprintf('alumni.%s.%02d@mmp.edu.np', $programCode, $alumniIndex));
    }

    protected function examName(string $programCode, int $semester, int $examIndex): string
    {
        return $this->assessmentExamName($programCode, $semester, $examIndex);
    }

    protected function assessmentExamName(string $programCode, int $semester, int $assessmentNumber): string
    {
        return sprintf('%s Semester %d Assessment %02d', $programCode, $semester, $assessmentNumber);
    }

    protected function ctevtFinalExamName(string $programCode, int $semester): string
    {
        return sprintf('%s Semester %d CTEVT Final', $programCode, $semester);
    }

    protected function subjectCode(string $programCode, int $semester, int $slot): string
    {
        return sprintf('%s-S%d-%02d', $programCode, $semester, $slot);
    }

    protected function subjectName(string $theme, string $label, int $semester): string
    {
        return sprintf('%s %s - Semester %d', $theme, $label, $semester);
    }

    protected function attendanceStatus(int $studentIndex, int $subjectIndex, int $semester, int $departmentIndex): string
    {
        $statuses = ['present', 'late', 'absent', 'excused'];

        return $statuses[($studentIndex + $subjectIndex + $semester + $departmentIndex) % count($statuses)];
    }

    protected function attendanceRemark(string $status): string
    {
        return match ($status) {
            'late' => 'Arrived after the bell.',
            'absent' => 'Marked absent for the session.',
            'excused' => 'Excused with prior notice.',
            default => 'Present on time.',
        };
    }

    protected function markStatus(int $studentIndex, int $subjectIndex, int $semester, int $examIndex): string
    {
        $statuses = ['draft', 'submitted', 'approved', 'published'];

        return $statuses[($studentIndex + $subjectIndex + $semester + $examIndex) % count($statuses)];
    }

    protected function markRemark(Subject $subject, bool $isAbsent, bool $isWithheld): string
    {
        if ($isAbsent) {
            return sprintf('%s student marked absent for demo data.', $subject->code);
        }

        if ($isWithheld) {
            return sprintf('%s result withheld for review.', $subject->code);
        }

        return sprintf('%s subject seeded for college workflow testing.', $subject->code);
    }

    protected function programScopeLabel(Program $program, int $semester): string
    {
        return trim(($program->code ? $program->code . ' - ' : '') . $program->name) . ' · Sem ' . $semester;
    }

    protected function departmentLabel(?Department $department): string
    {
        return $department?->code ? $department->code . ' - ' . $department->name : ($department?->name ?? 'All departments');
    }

    protected function subjectTypeLabel(string $type): string
    {
        return match ($type) {
            'practical' => 'Practical',
            'both' => 'Theory + Practical',
            default => 'Theory',
        };
    }

    protected function programForDepartmentCode(string $departmentCode): ?Program
    {
        return Program::query()->whereHas('department', fn ($query) => $query->where('code', $departmentCode))->first();
    }

    protected function subjectForProgramSemesterSlot(Program $program, int $semester, int $slot): ?Subject
    {
        return Subject::query()->where('program_id', $program->id)->where('semester', $semester)->where('code', $this->subjectCode($program->code, $semester, $slot))->first();
    }

    protected function studentQueryForProgramSemester(Program $program, int $semester)
    {
        return Student::query()->where('program_id', $program->id)->where('current_semester', $semester);
    }

    protected function examQueryForProgramSemester(Program $program, int $semester)
    {
        return \App\Models\Exam::query()
            ->where('department_id', $program->department_id)
            ->where(function ($query) use ($program, $semester) {
                $query->where('name', 'like', '%' . $program->code . ' Semester ' . $semester . ' Assessment %')
                    ->orWhere('name', $this->ctevtFinalExamName($program->code, $semester));
            });
    }

    protected function alumniSkills(string $programCode, int $alumniIndex): array
    {
        $skills = [
            'DIT' => ['Laravel', 'React', 'MySQL', 'APIs', 'DevOps'],
            'DAE' => ['AutoCAD', 'Revit', 'SketchUp', 'Site Planning', '3D Modeling'],
            'DCE' => ['Surveying', 'Structural Design', 'Estimation', 'Road Design', 'Project Supervision'],
            'DEL' => ['Circuit Design', 'PLC', 'Power Systems', 'Wiring', 'Renewables'],
            'DEE' => ['Embedded Systems', 'Signal Processing', 'IoT', 'PCB Design', 'Communication'],
            'DME' => ['Machine Design', 'Thermodynamics', 'CAD/CAM', 'Workshop Safety', 'Production'],
        ];

        return $skills[$programCode] ?? ['Technical Analysis', 'Project Coordination', 'Documentation'];
    }

    protected function alumniJobTitle(int $departmentIndex, int $alumniIndex): string
    {
        $jobs = ['Software Engineer', 'Project Officer', 'Site Supervisor', 'Lab Technician', 'System Analyst', 'Consultant'];

        return $jobs[($departmentIndex + $alumniIndex) % count($jobs)];
    }

    protected function alumniCompany(int $departmentIndex, int $alumniIndex): string
    {
        $companies = ['MMP Labs', 'Himalayan Works', 'Metro Infra', 'TechBridge Nepal', 'Future Systems', 'Green Energy Co.'];

        return $companies[($departmentIndex + $alumniIndex) % count($companies)];
    }

    protected function alumniLocation(int $departmentIndex): string
    {
        $locations = ['Kathmandu, Nepal', 'Lalitpur, Nepal', 'Bhaktapur, Nepal', 'Pokhara, Nepal', 'Biratnagar, Nepal', 'Chitwan, Nepal'];

        return $locations[$departmentIndex % count($locations)];
    }

    protected function guardianOccupation(int $departmentIndex, int $studentIndex): string
    {
        $occupations = ['Farmer', 'Business Owner', 'Teacher', 'Engineer', 'Technician', 'Civil Servant'];

        return $occupations[($departmentIndex + $studentIndex) % count($occupations)];
    }
}