<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AcademicSessionSemester;
use App\Models\Alumni;
use App\Models\Department;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $demo = new DemoDataSeeder();

        DB::transaction(function () use ($demo): void {
            $principal = $demo->seedUser('Dr. Principal', 'principal@mmp.edu.np', 'principal');
            $hod = $demo->seedUser('Er. Yubraj Chaudhary', 'hod.it@mmp.edu.np', 'hod');
            $teacherUser = $demo->seedUser('Er. Anil Khatri', 'teacher.it@mmp.edu.np', 'teacher');
            $studentOneUser = $demo->seedUser('Sita Karki', 'student01@mmp.edu.np', 'student');
            $studentTwoUser = $demo->seedUser('Rajan Thapa', 'student02@mmp.edu.np', 'student');
            $parentUser = $demo->seedUser('Gita Karki', 'parent01@mmp.edu.np', 'parent');
            $alumniUser = $demo->seedUser('Dipesh Shrestha', 'alumni01@mmp.edu.np', 'alumni');

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

            $semesterPlan = [
                [
                    'semester_number' => 1,
                    'start_date' => now()->subDays(45)->toDateString(),
                    'end_date' => now()->addDays(75)->toDateString(),
                    'status' => 'running',
                    'delay_reason' => null,
                    'is_active' => true,
                    'notes' => 'Regular intake semester is running as planned.',
                ],
                [
                    'semester_number' => 3,
                    'start_date' => now()->subDays(30)->toDateString(),
                    'end_date' => now()->addDays(55)->toDateString(),
                    'status' => 'running',
                    'delay_reason' => null,
                    'is_active' => true,
                    'notes' => 'Mid-level batch currently in regular run.',
                ],
                [
                    'semester_number' => 5,
                    'start_date' => now()->subDays(70)->toDateString(),
                    'end_date' => now()->addDays(18)->toDateString(),
                    'status' => 'delayed',
                    'delay_reason' => 'exam_late',
                    'is_active' => true,
                    'notes' => 'Delay due to exam publication and practical board schedule.',
                ],
                [
                    'semester_number' => 4,
                    'start_date' => now()->subDays(15)->toDateString(),
                    'end_date' => now()->addDays(90)->toDateString(),
                    'status' => 'delayed',
                    'delay_reason' => 'internal_delay',
                    'is_active' => true,
                    'notes' => 'Late start caused by internal timetable realignment.',
                ],
            ];

            AcademicSessionSemester::query()
                ->where('academic_session_id', $session->id)
                ->whereNotIn('semester_number', collect($semesterPlan)->pluck('semester_number')->all())
                ->delete();

            foreach ($semesterPlan as $semesterSetup) {
                AcademicSessionSemester::query()->updateOrCreate(
                    [
                        'academic_session_id' => $session->id,
                        'semester_number' => $semesterSetup['semester_number'],
                    ],
                    $semesterSetup
                );
            }

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
            $demo->restoreIfTrashed($department);

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
            $demo->restoreIfTrashed($program);

            $courseCatalog = [
                [
                    'department_code' => 'AR',
                    'department_name' => 'Architecture Engineering',
                    'program_code' => 'DAE',
                    'program_name' => 'Diploma in Architecture Engineering',
                    'description' => 'Architectural design, drafting, building planning and construction management.',
                ],
                [
                    'department_code' => 'CE',
                    'department_name' => 'Civil Engineering',
                    'program_code' => 'DCE',
                    'program_name' => 'Diploma in Civil Engineering',
                    'description' => 'Design, construction and maintenance of infrastructure including roads, bridges and buildings.',
                ],
                [
                    'department_code' => 'EL',
                    'department_name' => 'Electrical Engineering',
                    'program_code' => 'DEL',
                    'program_name' => 'Diploma in Electrical Engineering',
                    'description' => 'Electrical systems, power generation, wiring, switchgear and electrical installations.',
                ],
                [
                    'department_code' => 'EE',
                    'department_name' => 'Electronics Engineering',
                    'program_code' => 'DEE',
                    'program_name' => 'Diploma in Electronics Engineering',
                    'description' => 'Electronics circuits, communication systems, embedded systems and signal processing.',
                ],
                [
                    'department_code' => 'ME',
                    'department_name' => 'Mechanical Engineering',
                    'program_code' => 'DME',
                    'program_name' => 'Diploma in Mechanical Engineering',
                    'description' => 'Machine design, manufacturing, thermodynamics and mechanical systems.',
                ],
            ];

            foreach ($courseCatalog as $course) {
                $courseDepartment = Department::withTrashed()->updateOrCreate(
                    ['code' => $course['department_code']],
                    [
                        'name' => $course['department_name'],
                        'slug' => Str::slug($course['department_name']),
                        'description' => $course['description'],
                        'photo' => null,
                        'syllabus' => null,
                        'seat_capacity' => 40,
                        'hod_id' => null,
                        'is_active' => true,
                    ]
                );
                $demo->restoreIfTrashed($courseDepartment);

                $courseProgram = Program::withTrashed()->updateOrCreate(
                    ['code' => $course['program_code']],
                    [
                        'department_id' => $courseDepartment->id,
                        'name' => $course['program_name'],
                        'slug' => Str::slug($course['program_name']),
                        'total_semesters' => 6,
                        'duration_years' => 3,
                        'description' => $course['description'],
                        'is_active' => true,
                    ]
                );
                $demo->restoreIfTrashed($courseProgram);
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
            $demo->restoreIfTrashed($teacher);

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
            $demo->restoreIfTrashed($studentOne);

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
            $demo->restoreIfTrashed($studentTwo);

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
            $demo->restoreIfTrashed($alumni);

            $teacher->subjects()->syncWithoutDetaching([
                $subjects['CG501']->id => ['academic_session_id' => $session->id, 'section' => 'A'],
                $subjects['WT502']->id => ['academic_session_id' => $session->id, 'section' => 'A'],
                $subjects['DBMS503']->id => ['academic_session_id' => $session->id, 'section' => 'A'],
            ]);
        });
    }
}
