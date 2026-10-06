<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AcademicSessionSemester;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Seeds: academic_sessions, academic_session_semesters,
 *        programs (DIT, DEEE, DCE, DME), subjects (4 per semester × 6 semesters).
 */
class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Academic Session ──────────────────────────────────────────────
        $session = AcademicSession::firstOrCreate(
            ['name' => '2081-2082'],
            [
                'name_bs'      => '२०८१-२०८२',
                'start_date'   => '2024-07-17',
                'end_date'     => '2025-07-16',
                'is_active'    => true,
                'status'       => 'active',
                'is_locked'    => false,
                'activated_at' => now(),
                'notes'        => 'Current academic year 2081-2082 BS',
            ]
        );

        // ── 2. Semester schedule (6 semesters) ──────────────────────────────
        $semesterSchedule = [
            1 => ['2024-07-17', '2024-10-15', 'running'],
            2 => ['2024-10-16', '2025-01-14', 'upcoming'],
            3 => ['2025-01-15', '2025-04-14', 'upcoming'],
            4 => ['2025-04-15', '2025-07-14', 'upcoming'],
            5 => ['2025-07-15', '2025-10-13', 'upcoming'],
            6 => ['2025-10-14', '2026-01-12', 'upcoming'],
        ];

        foreach ($semesterSchedule as $num => [$start, $end, $status]) {
            AcademicSessionSemester::firstOrCreate(
                ['academic_session_id' => $session->id, 'semester_number' => $num],
                [
                    'start_date' => $start,
                    'end_date'   => $end,
                    'status'     => $status,
                    'is_active'  => $status === 'running',
                ]
            );
        }

        // ── 3. Programs with subjects ────────────────────────────────────────
        $programsData = [
            'IT' => [
                'program' => [
                    'name'            => 'Diploma in Information Technology',
                    'code'            => 'DIT',
                    'slug'            => 'diploma-in-information-technology',
                    'ctevt_code'      => 'DIT-001',
                    'affiliation_type'=> 'CTEVT',
                    'total_semesters' => 6,
                    'duration'        => 3,
                    'duration_years'  => 3,
                    'description'     => 'A 3-year CTEVT diploma program in Information Technology covering programming, networking, and database management.',
                    'eligibility'     => 'SEE pass with minimum C grade in Mathematics and Science',
                    'is_active'       => true,
                ],
                'subjects' => [
                    1 => [
                        ['name' => 'English I',                      'code' => 'ENG101', 'type' => 'theory'],
                        ['name' => 'Mathematics I',                  'code' => 'MTH101', 'type' => 'theory'],
                        ['name' => 'Computer Fundamentals',          'code' => 'IT101',  'type' => 'both'],
                        ['name' => 'Office Application Lab',         'code' => 'IT102',  'type' => 'practical'],
                    ],
                    2 => [
                        ['name' => 'English II',                     'code' => 'ENG201', 'type' => 'theory'],
                        ['name' => 'Mathematics II',                 'code' => 'MTH201', 'type' => 'theory'],
                        ['name' => 'Programming in C',               'code' => 'IT201',  'type' => 'both'],
                        ['name' => 'Web Technology I',               'code' => 'IT202',  'type' => 'both'],
                    ],
                    3 => [
                        ['name' => 'Object Oriented Programming',    'code' => 'IT301',  'type' => 'both'],
                        ['name' => 'Database Management System',     'code' => 'IT302',  'type' => 'both'],
                        ['name' => 'Computer Networks',              'code' => 'IT303',  'type' => 'theory'],
                        ['name' => 'Web Technology II',              'code' => 'IT304',  'type' => 'both'],
                    ],
                    4 => [
                        ['name' => 'Software Engineering',           'code' => 'IT401',  'type' => 'theory'],
                        ['name' => 'Advanced Database',              'code' => 'IT402',  'type' => 'both'],
                        ['name' => 'Operating Systems',              'code' => 'IT403',  'type' => 'both'],
                        ['name' => 'Mobile Application Development', 'code' => 'IT404',  'type' => 'both'],
                    ],
                    5 => [
                        ['name' => 'Cyber Security',                 'code' => 'IT501',  'type' => 'theory'],
                        ['name' => 'Cloud Computing',                'code' => 'IT502',  'type' => 'both'],
                        ['name' => 'Project Management',             'code' => 'IT503',  'type' => 'theory'],
                        ['name' => 'Minor Project',                  'code' => 'IT504',  'type' => 'practical'],
                    ],
                    6 => [
                        ['name' => 'Artificial Intelligence',        'code' => 'IT601',  'type' => 'theory'],
                        ['name' => 'Entrepreneurship',               'code' => 'IT602',  'type' => 'theory'],
                        ['name' => 'Major Project',                  'code' => 'IT603',  'type' => 'practical'],
                        ['name' => 'Industrial Attachment',          'code' => 'IT604',  'type' => 'practical'],
                    ],
                ],
            ],
            'CIVIL' => [
                'program' => [
                    'name'            => 'Diploma in Civil Engineering',
                    'code'            => 'DCE',
                    'slug'            => 'diploma-in-civil-engineering',
                    'ctevt_code'      => 'DCE-001',
                    'affiliation_type'=> 'CTEVT',
                    'total_semesters' => 6,
                    'duration'        => 3,
                    'duration_years'  => 3,
                    'description'     => 'A 3-year CTEVT diploma program in Civil Engineering covering structural design, surveying, and construction management.',
                    'eligibility'     => 'SEE pass with minimum C grade in Mathematics and Science',
                    'is_active'       => true,
                ],
                'subjects' => [
                    1 => [
                        ['name' => 'Engineering Drawing I',          'code' => 'CE101', 'type' => 'practical'],
                        ['name' => 'Applied Mathematics I',          'code' => 'CE102', 'type' => 'theory'],
                        ['name' => 'Engineering Physics',            'code' => 'CE103', 'type' => 'both'],
                        ['name' => 'Basic Civil Engineering',        'code' => 'CE104', 'type' => 'theory'],
                    ],
                    2 => [
                        ['name' => 'Engineering Drawing II',         'code' => 'CE201', 'type' => 'practical'],
                        ['name' => 'Applied Mathematics II',         'code' => 'CE202', 'type' => 'theory'],
                        ['name' => 'Building Materials',             'code' => 'CE203', 'type' => 'theory'],
                        ['name' => 'Surveying I',                    'code' => 'CE204', 'type' => 'both'],
                    ],
                    3 => [
                        ['name' => 'Strength of Materials',          'code' => 'CE301', 'type' => 'theory'],
                        ['name' => 'Surveying II',                   'code' => 'CE302', 'type' => 'both'],
                        ['name' => 'Fluid Mechanics',                'code' => 'CE303', 'type' => 'both'],
                        ['name' => 'Concrete Technology',            'code' => 'CE304', 'type' => 'both'],
                    ],
                    4 => [
                        ['name' => 'Structural Analysis',            'code' => 'CE401', 'type' => 'theory'],
                        ['name' => 'Irrigation Engineering',         'code' => 'CE402', 'type' => 'theory'],
                        ['name' => 'Highway Engineering',            'code' => 'CE403', 'type' => 'both'],
                        ['name' => 'Estimating & Costing',           'code' => 'CE404', 'type' => 'theory'],
                    ],
                    5 => [
                        ['name' => 'Design of RCC Structures',       'code' => 'CE501', 'type' => 'theory'],
                        ['name' => 'Geotechnical Engineering',       'code' => 'CE502', 'type' => 'both'],
                        ['name' => 'Construction Management',        'code' => 'CE503', 'type' => 'theory'],
                        ['name' => 'Minor Project',                  'code' => 'CE504', 'type' => 'practical'],
                    ],
                    6 => [
                        ['name' => 'Environmental Engineering',      'code' => 'CE601', 'type' => 'theory'],
                        ['name' => 'Earthquake Engineering',         'code' => 'CE602', 'type' => 'theory'],
                        ['name' => 'Major Project',                  'code' => 'CE603', 'type' => 'practical'],
                        ['name' => 'Industrial Attachment',          'code' => 'CE604', 'type' => 'practical'],
                    ],
                ],
            ],
            'ELECTRICAL' => [
                'program' => [
                    'name'            => 'Diploma in Electrical Engineering',
                    'code'            => 'DEEE',
                    'slug'            => 'diploma-in-electrical-engineering',
                    'ctevt_code'      => 'DEEE-001',
                    'affiliation_type'=> 'CTEVT',
                    'total_semesters' => 6,
                    'duration'        => 3,
                    'duration_years'  => 3,
                    'description'     => 'A 3-year CTEVT diploma program in Electrical Engineering covering circuit theory, power systems, and electronics.',
                    'eligibility'     => 'SEE pass with minimum C grade in Mathematics and Science',
                    'is_active'       => true,
                ],
                'subjects' => [
                    1 => [
                        ['name' => 'Engineering Drawing',            'code' => 'EE101', 'type' => 'practical'],
                        ['name' => 'Applied Mathematics I',          'code' => 'EE102', 'type' => 'theory'],
                        ['name' => 'Basic Electricity',              'code' => 'EE103', 'type' => 'both'],
                        ['name' => 'Workshop Technology',            'code' => 'EE104', 'type' => 'practical'],
                    ],
                    2 => [
                        ['name' => 'Circuit Theory',                 'code' => 'EE201', 'type' => 'both'],
                        ['name' => 'Applied Mathematics II',         'code' => 'EE202', 'type' => 'theory'],
                        ['name' => 'Electronics I',                  'code' => 'EE203', 'type' => 'both'],
                        ['name' => 'Electrical Measurement',         'code' => 'EE204', 'type' => 'both'],
                    ],
                    3 => [
                        ['name' => 'Electronics II',                 'code' => 'EE301', 'type' => 'both'],
                        ['name' => 'Electrical Machines I',          'code' => 'EE302', 'type' => 'both'],
                        ['name' => 'Digital Electronics',            'code' => 'EE303', 'type' => 'both'],
                        ['name' => 'Network Analysis',               'code' => 'EE304', 'type' => 'theory'],
                    ],
                    4 => [
                        ['name' => 'Electrical Machines II',         'code' => 'EE401', 'type' => 'both'],
                        ['name' => 'Power Systems',                  'code' => 'EE402', 'type' => 'theory'],
                        ['name' => 'Microprocessor',                 'code' => 'EE403', 'type' => 'both'],
                        ['name' => 'Control Systems',                'code' => 'EE404', 'type' => 'theory'],
                    ],
                    5 => [
                        ['name' => 'Power Electronics',              'code' => 'EE501', 'type' => 'both'],
                        ['name' => 'Renewable Energy',               'code' => 'EE502', 'type' => 'theory'],
                        ['name' => 'Industrial Automation',          'code' => 'EE503', 'type' => 'both'],
                        ['name' => 'Minor Project',                  'code' => 'EE504', 'type' => 'practical'],
                    ],
                    6 => [
                        ['name' => 'High Voltage Engineering',       'code' => 'EE601', 'type' => 'theory'],
                        ['name' => 'Electrical Estimation',          'code' => 'EE602', 'type' => 'theory'],
                        ['name' => 'Major Project',                  'code' => 'EE603', 'type' => 'practical'],
                        ['name' => 'Industrial Attachment',          'code' => 'EE604', 'type' => 'practical'],
                    ],
                ],
            ],
            'MECHANICAL' => [
                'program' => [
                    'name'            => 'Diploma in Mechanical Engineering',
                    'code'            => 'DME',
                    'slug'            => 'diploma-in-mechanical-engineering',
                    'ctevt_code'      => 'DME-001',
                    'affiliation_type'=> 'CTEVT',
                    'total_semesters' => 6,
                    'duration'        => 3,
                    'duration_years'  => 3,
                    'description'     => 'A 3-year CTEVT diploma program in Mechanical Engineering covering thermodynamics, manufacturing, and machine design.',
                    'eligibility'     => 'SEE pass with minimum C grade in Mathematics and Science',
                    'is_active'       => true,
                ],
                'subjects' => [
                    1 => [
                        ['name' => 'Engineering Drawing I',          'code' => 'ME101', 'type' => 'practical'],
                        ['name' => 'Applied Mathematics I',          'code' => 'ME102', 'type' => 'theory'],
                        ['name' => 'Engineering Physics',            'code' => 'ME103', 'type' => 'both'],
                        ['name' => 'Workshop Technology',            'code' => 'ME104', 'type' => 'practical'],
                    ],
                    2 => [
                        ['name' => 'Engineering Drawing II',         'code' => 'ME201', 'type' => 'practical'],
                        ['name' => 'Applied Mathematics II',         'code' => 'ME202', 'type' => 'theory'],
                        ['name' => 'Engineering Materials',          'code' => 'ME203', 'type' => 'theory'],
                        ['name' => 'Manufacturing Process I',        'code' => 'ME204', 'type' => 'both'],
                    ],
                    3 => [
                        ['name' => 'Thermodynamics I',               'code' => 'ME301', 'type' => 'theory'],
                        ['name' => 'Fluid Mechanics',                'code' => 'ME302', 'type' => 'both'],
                        ['name' => 'Machine Elements',               'code' => 'ME303', 'type' => 'theory'],
                        ['name' => 'Manufacturing Process II',       'code' => 'ME304', 'type' => 'both'],
                    ],
                    4 => [
                        ['name' => 'Thermodynamics II',              'code' => 'ME401', 'type' => 'theory'],
                        ['name' => 'Heat Transfer',                  'code' => 'ME402', 'type' => 'theory'],
                        ['name' => 'Machine Design',                 'code' => 'ME403', 'type' => 'theory'],
                        ['name' => 'CNC Technology',                 'code' => 'ME404', 'type' => 'both'],
                    ],
                    5 => [
                        ['name' => 'Power Plant Engineering',        'code' => 'ME501', 'type' => 'theory'],
                        ['name' => 'Industrial Management',          'code' => 'ME502', 'type' => 'theory'],
                        ['name' => 'Refrigeration & AC',             'code' => 'ME503', 'type' => 'both'],
                        ['name' => 'Minor Project',                  'code' => 'ME504', 'type' => 'practical'],
                    ],
                    6 => [
                        ['name' => 'Automobile Engineering',         'code' => 'ME601', 'type' => 'theory'],
                        ['name' => 'Quality Control',                'code' => 'ME602', 'type' => 'theory'],
                        ['name' => 'Major Project',                  'code' => 'ME603', 'type' => 'practical'],
                        ['name' => 'Industrial Attachment',          'code' => 'ME604', 'type' => 'practical'],
                    ],
                ],
            ],
        ];

        foreach ($programsData as $deptCode => $data) {
            $department = Department::where('code', $deptCode)->first();
            if (!$department) continue;

            $program = Program::firstOrCreate(
                ['code' => $data['program']['code']],
                array_merge($data['program'], ['department_id' => $department->id])
            );

            foreach ($data['subjects'] as $semester => $subjects) {
                foreach ($subjects as $subjectData) {
                    Subject::firstOrCreate(
                        ['code' => $subjectData['code']],
                        [
                            'program_id'                     => $program->id,
                            'semester'                       => $semester,
                            'name'                           => $subjectData['name'],
                            'type'                           => $subjectData['type'],
                            'full_marks_internal_theory'     => 20,
                            'full_marks_external_theory'     => 80,
                            'pass_marks_internal_theory'     => 8,
                            'pass_marks_external_theory'     => 32,
                            'full_marks_internal_practical'  => 30,
                            'full_marks_external_practical'  => 20,
                            'pass_marks_internal_practical'  => 15,
                            'pass_marks_external_practical'  => 10,
                            'credit_hours'                   => in_array($subjectData['type'], ['practical']) ? 2 : 3,
                            'is_active'                      => true,
                        ]
                    );
                }
            }
        }

        $this->command->info('Academic sessions, semesters, programs, and subjects seeded successfully.');
    }
}
