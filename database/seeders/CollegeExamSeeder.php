<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Exam;
use App\Models\Program;
use Illuminate\Database\Seeder;

class CollegeExamSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        $session = $this->collegeSession();

        foreach ($this->departmentCatalog() as $departmentIndex => $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();
            $program = Program::query()->where('code', $profile['program_code'])->first();

            if (! $department || ! $program) {
                continue;
            }

            for ($semester = 1; $semester <= self::SEMESTER_COUNT; $semester++) {
                for ($examIndex = 1; $examIndex <= self::MONTHLY_ASSESSMENTS_PER_SEMESTER; $examIndex++) {
                    $exam = Exam::withTrashed()->updateOrCreate(
                        [
                            'academic_session_id' => $session->id,
                            'department_id' => $department->id,
                            'name' => $this->assessmentExamName($program->code, $semester, $examIndex),
                        ],
                        [
                            'type' => 'internal',
                            'category' => 'monthly_assessment',
                            'assessment_number' => $examIndex,
                            'start_date' => now()->subWeeks(24 - ($semester * 2) - $examIndex)->toDateString(),
                            'end_date' => now()->subWeeks(23 - ($semester * 2) - $examIndex)->toDateString(),
                            'status' => 'results_published',
                            'marks_open' => false,
                            'is_published' => true,
                            'published_at' => now()->subWeeks(max(1, 20 - ($semester + $examIndex))),
                        ]
                    );

                    $this->restoreIfTrashed($exam);

                    $exam->programs()->syncWithoutDetaching([
                        $program->id => ['semester' => $semester],
                    ]);
                }

                $finalExam = Exam::withTrashed()->updateOrCreate(
                    [
                        'academic_session_id' => $session->id,
                        'department_id' => $department->id,
                        'name' => $this->ctevtFinalExamName($program->code, $semester),
                    ],
                    [
                        'type' => 'regular',
                        'category' => 'ctevt_final',
                        'assessment_number' => null,
                        'start_date' => now()->subWeeks(8 - min($semester, 4))->toDateString(),
                        'end_date' => now()->subWeeks(7 - min($semester, 4))->toDateString(),
                        'status' => 'completed',
                        'marks_open' => true,
                        'is_published' => false,
                        'published_at' => null,
                    ]
                );

                $this->restoreIfTrashed($finalExam);

                $finalExam->programs()->syncWithoutDetaching([
                    $program->id => ['semester' => $semester],
                ]);
            }
        }

        $this->normalizeExamCategoryByName($session->id);
        $this->ensureFinalExamsForAssessmentOnlySemesters($session->id);
    }

    private function normalizeExamCategoryByName(int $sessionId): void
    {
        $exams = Exam::query()
            ->where('academic_session_id', $sessionId)
            ->where(function ($query) {
                $query->where('name', 'like', '%Assessment%')
                    ->orWhere('name', 'like', '%CTEVT Final%');
            })
            ->get(['id', 'name', 'category', 'type', 'assessment_number']);

        foreach ($exams as $exam) {
            $name = (string) $exam->name;

            if (stripos($name, 'ctevt final') !== false) {
                $exam->forceFill([
                    'category' => 'ctevt_final',
                    'type' => 'regular',
                    'assessment_number' => null,
                ])->save();

                continue;
            }

            if (stripos($name, 'assessment') !== false) {
                $assessmentNumber = null;

                if (preg_match('/Assessment\s*(\d+)/i', $name, $matches)) {
                    $assessmentNumber = (int) $matches[1];
                }

                $exam->forceFill([
                    'category' => 'monthly_assessment',
                    'type' => 'internal',
                    'assessment_number' => $assessmentNumber,
                ])->save();
            }
        }
    }

    private function ensureFinalExamsForAssessmentOnlySemesters(int $sessionId): void
    {
        $assessmentAssignments = Exam::query()
            ->select(['exams.id', 'exams.department_id'])
            ->where('academic_session_id', $sessionId)
            ->where('category', 'monthly_assessment')
            ->with([
                'programs' => fn ($query) => $query->select('programs.id', 'programs.code'),
            ])
            ->get();

        foreach ($assessmentAssignments as $assessmentExam) {
            foreach ($assessmentExam->programs as $program) {
                $semester = (int) ($program->pivot->semester ?? 0);

                if ($semester <= 0) {
                    continue;
                }

                $hasFinal = Exam::query()
                    ->where('academic_session_id', $sessionId)
                    ->where('department_id', $assessmentExam->department_id)
                    ->where('category', 'ctevt_final')
                    ->whereHas('programs', fn ($query) => $query
                        ->where('programs.id', $program->id)
                        ->where('exam_program.semester', $semester))
                    ->exists();

                if ($hasFinal) {
                    continue;
                }

                $finalExam = Exam::withTrashed()->updateOrCreate(
                    [
                        'academic_session_id' => $sessionId,
                        'department_id' => $assessmentExam->department_id,
                        'name' => $this->ctevtFinalExamName($program->code, $semester),
                    ],
                    [
                        'type' => 'regular',
                        'category' => 'ctevt_final',
                        'assessment_number' => null,
                        'start_date' => now()->subWeeks(8 - min($semester, 4))->toDateString(),
                        'end_date' => now()->subWeeks(7 - min($semester, 4))->toDateString(),
                        'status' => 'completed',
                        'marks_open' => true,
                        'is_published' => false,
                        'published_at' => null,
                    ]
                );

                $this->restoreIfTrashed($finalExam);

                $finalExam->programs()->syncWithoutDetaching([
                    $program->id => ['semester' => $semester],
                ]);
            }
        }
    }
}