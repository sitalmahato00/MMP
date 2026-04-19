<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class CollegeMarkSeeder extends Seeder
{
    use CollegeDemoBaseSeeder;

    public function run(): void
    {
        foreach ($this->departmentCatalog() as $departmentIndex => $profile) {
            $department = Department::query()->where('code', $profile['department_code'])->first();
            $program = Program::query()->where('code', $profile['program_code'])->first();

            if (! $department || ! $program) {
                continue;
            }

            for ($semester = 1; $semester <= self::SEMESTER_COUNT; $semester++) {
                $students = Student::query()
                    ->where('program_id', $program->id)
                    ->where('current_semester', $semester)
                    ->orderBy('id')
                    ->get();

                $subjects = Subject::query()
                    ->where('program_id', $program->id)
                    ->where('semester', $semester)
                    ->orderBy('id')
                    ->get();

                $exams = Exam::query()
                    ->where('academic_session_id', $this->collegeSession()->id)
                    ->where('department_id', $department->id)
                    ->where(function ($query) use ($program, $semester) {
                        $query->where('name', 'like', $program->code . ' Semester ' . $semester . ' Assessment %')
                            ->orWhere('name', $this->ctevtFinalExamName($program->code, $semester));
                    })
                    ->orderByRaw("CASE WHEN category = 'monthly_assessment' THEN 0 ELSE 1 END")
                    ->orderBy('assessment_number')
                    ->orderBy('id')
                    ->get();

                foreach ($exams as $examIndex => $exam) {
                    $rows = [];

                    foreach ($subjects as $subjectIndex => $subject) {
                        $teacher = Teacher::query()
                            ->where('department_id', $department->id)
                            ->whereHas('subjects', fn ($query) => $query->where('subjects.id', $subject->id))
                            ->first();

                        foreach ($students as $studentIndex => $student) {
                            $offset = $studentIndex + $subjectIndex + $semester + $examIndex + $departmentIndex;
                            $isAbsent = $offset % 13 === 0;
                            $isWithheld = ! $isAbsent && $offset % 17 === 0;
                            $marks = $this->markValues($exam, $subject, $studentIndex, $subjectIndex, $semester, $examIndex, $isAbsent, $isWithheld);

                            $rows[] = array_merge([
                                'exam_id' => $exam->id,
                                'student_id' => $student->id,
                                'subject_id' => $subject->id,
                                'program_id' => $subject->program_id,
                                'teacher_id' => $teacher?->id,
                                'semester' => $subject->semester,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ], $marks);
                        }
                    }

                    foreach (array_chunk($rows, 1000) as $chunk) {
                        Mark::upsert($chunk, ['exam_id', 'student_id', 'subject_id'], [
                            'program_id',
                            'teacher_id',
                            'semester',
                            'internal_theory_marks',
                            'external_theory_marks',
                            'internal_practical_marks',
                            'external_practical_marks',
                            'assessment_attendance_percent',
                            'assessment_full_marks',
                            'assessment_pass_marks',
                            'assessment_obtained_marks',
                            'is_absent',
                            'is_withheld',
                            'is_delayed',
                            'delay_reason',
                            'status',
                            'remarks',
                            'updated_at',
                        ]);
                    }
                }
            }
        }
    }

    private function markValues(Exam $exam, Subject $subject, int $studentIndex, int $subjectIndex, int $semester, int $examIndex, bool $isAbsent, bool $isWithheld): array
    {
        $status = $this->markStatus($studentIndex, $subjectIndex, $semester, $examIndex);

        if ($isAbsent || $isWithheld) {
            $status = 'published';
        }

        if ($exam->category === 'monthly_assessment') {
            $fullMarks = 40.0;
            $passMarks = 16.0;
            $attendancePercent = $isAbsent ? 0.0 : (75 + (($studentIndex + $subjectIndex + $examIndex) % 21));
            $obtainedMarks = $isAbsent || $isWithheld ? null : (22 + (($studentIndex + $semester + $subjectIndex + $examIndex) % 16));

            return [
                'internal_theory_marks' => null,
                'external_theory_marks' => null,
                'internal_practical_marks' => null,
                'external_practical_marks' => null,
                'assessment_attendance_percent' => $attendancePercent,
                'assessment_full_marks' => $fullMarks,
                'assessment_pass_marks' => $passMarks,
                'assessment_obtained_marks' => $obtainedMarks,
                'is_absent' => $isAbsent,
                'is_withheld' => $isWithheld,
                'is_delayed' => false,
                'delay_reason' => null,
                'status' => 'published',
                'remarks' => $this->markRemark($subject, $isAbsent, $isWithheld),
            ];
        }

        $isDelayed = ! $isAbsent && ! $isWithheld && (($studentIndex + $subjectIndex + $semester) % 19 === 0);

        if ($subject->type === 'practical') {
            return [
                'internal_theory_marks' => null,
                'external_theory_marks' => null,
                'internal_practical_marks' => $isAbsent || $isWithheld ? null : 18 + (($studentIndex + $semester + $subjectIndex + $examIndex) % 8),
                'external_practical_marks' => $isAbsent || $isWithheld || $isDelayed ? null : 12 + (($studentIndex * 2 + $subjectIndex + $examIndex) % 7),
                'assessment_attendance_percent' => null,
                'assessment_full_marks' => null,
                'assessment_pass_marks' => null,
                'assessment_obtained_marks' => null,
                'is_absent' => $isAbsent,
                'is_withheld' => $isWithheld,
                'is_delayed' => $isDelayed,
                'delay_reason' => $isDelayed ? 'External practical evaluation pending from board examiner.' : null,
                'status' => $isDelayed ? 'approved' : $status,
                'remarks' => $this->markRemark($subject, $isAbsent, $isWithheld),
            ];
        }

        $internalTheory = 13 + (($studentIndex + $semester + $subjectIndex + $examIndex) % 6);
        $externalTheory = 52 + (($studentIndex * 2 + $semester + $subjectIndex + $examIndex) % 18);
        $internalPractical = 18 + (($studentIndex + $semester + $subjectIndex + $examIndex) % 8);
        $externalPractical = 12 + (($studentIndex * 2 + $subjectIndex + $examIndex) % 7);

        return [
            'internal_theory_marks' => $isAbsent || $isWithheld ? null : $internalTheory,
            'external_theory_marks' => $isAbsent || $isWithheld || $isDelayed ? null : $externalTheory,
            'internal_practical_marks' => $subject->type === 'theory' ? null : ($isAbsent || $isWithheld ? null : $internalPractical),
            'external_practical_marks' => $subject->type === 'theory' ? null : ($isAbsent || $isWithheld || $isDelayed ? null : $externalPractical),
            'assessment_attendance_percent' => null,
            'assessment_full_marks' => null,
            'assessment_pass_marks' => null,
            'assessment_obtained_marks' => null,
            'is_absent' => $isAbsent,
            'is_withheld' => $isWithheld,
            'is_delayed' => $isDelayed,
            'delay_reason' => $isDelayed ? 'External marks delayed until CTEVT final board sheet arrives.' : null,
            'status' => $isDelayed ? 'approved' : $status,
            'remarks' => $this->markRemark($subject, $isAbsent, $isWithheld),
        ];
    }
}