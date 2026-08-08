<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentRecordService
{
    private array $markingSchemeCache = [];

    public function getVisiblePublishedMarks(Student $student, array $with = []): Collection
    {
        $relations = array_values(array_unique(array_merge(['exam', 'subject'], $with)));

        return $student->marks()
            ->visibleToPortal()
            ->with($relations)
            ->get();
    }

    public function summarizeMarks(Collection $marks): array
    {
        $marks = $marks
            ->filter(fn (Mark $mark) => $mark->exam?->isPublishedState)
            ->values();

        $gradeDistribution = [
            'distinction' => 0,
            'first_division' => 0,
            'second_division' => 0,
            'third_division' => 0,
            'fail' => 0,
        ];

        $assessmentResults = $this->buildAssessmentResults($marks);
        $totalObtained = 0.0;
        $totalFull = 0.0;

        foreach ($marks as $mark) {
            $displayMetrics = $this->getMarkDisplayMetrics($mark);

            $totalObtained += $displayMetrics['obtained_marks'];
            $totalFull += $displayMetrics['full_marks'];
        }

        foreach ($assessmentResults as $result) {
            $this->incrementGradeDistribution($gradeDistribution, (float) ($result['percentage'] ?? 0));
        }

        return [
            'average_marks' => $totalFull > 0
                ? round(($totalObtained / $totalFull) * 100, 1)
                : null,
            'percentage_rate' => $totalFull > 0
                ? round(($totalObtained / $totalFull) * 100, 1)
                : null,
            'total_results' => $marks->count(),
            'total_subjects' => $marks->pluck('subject_id')->filter()->unique()->count(),
            'total_exams' => $marks->pluck('exam_id')->filter()->unique()->count(),
            'total_assessments' => $assessmentResults->count(),
            'distinction_assessments' => $assessmentResults->filter(fn (array $result) => ($result['percentage'] ?? 0) >= 80)->count(),
            'grade_distribution' => $gradeDistribution,
        ];
    }

    public function buildAssessmentResults(Collection $marks): Collection
    {
        $marks = $marks
            ->filter(fn (Mark $mark) => $mark->exam?->isPublishedState)
            ->values();

        return $marks
            ->groupBy('exam_id')
            ->map(function (Collection $examMarks) {
                $exam = $examMarks->first()->exam;
                $totalObtained = 0.0;
                $totalFull = 0.0;
                $allPassed = true;

                foreach ($examMarks as $mark) {
                    $displayMetrics = $this->getMarkDisplayMetrics($mark);

                    $totalObtained += $displayMetrics['obtained_marks'];
                    $totalFull += $displayMetrics['full_marks'];
                    $allPassed = $allPassed && $displayMetrics['passed'];
                }

                $percentage = $totalFull > 0
                    ? round(($totalObtained / $totalFull) * 100, 2)
                    : 0.0;

                return [
                    'exam' => $exam,
                    'marks' => $examMarks->values(),
                    'marks_count' => $examMarks->count(),
                    'total_obtained' => round($totalObtained, 2),
                    'total_full' => round($totalFull, 2),
                    'percentage' => $percentage,
                    'passed' => $allPassed,
                    'division' => $this->gradeLabel($percentage),
                    'published_at' => $exam?->published_at ?? $exam?->created_at,
                ];
            })
            ->sortByDesc(function (array $result) {
                $publishedAt = $result['published_at'] ?? null;

                return $publishedAt instanceof CarbonInterface
                    ? $publishedAt->getTimestamp()
                    : 0;
            })
            ->values();
    }

    public function getPublishedMarksheet(Student $student, Exam $exam): array
    {
        $marks = $student->marks()
            ->visibleToPortal()
            ->where('exam_id', $exam->id)
            ->with(['subject', 'teacher.user', 'exam'])
            ->get();

        $marksData = $marks->map(function (Mark $mark) {
            $displayMetrics = $this->getMarkDisplayMetrics($mark);

            return [
                'mark' => $mark,
                'scheme' => $this->markingSchemeForMark($mark),
                'full_marks' => $displayMetrics['full_marks'],
                'pass_marks' => $displayMetrics['pass_marks'],
                'obtained_marks' => $displayMetrics['obtained_marks'],
                'percentage' => $displayMetrics['percentage'],
                'passed' => $displayMetrics['passed'],
            ];
        });

        $totalObtained = round($marksData->sum('obtained_marks'), 2);
        $totalFull = round($marksData->sum('full_marks'), 2);
        $percentage = $totalFull > 0
            ? round(($totalObtained / $totalFull) * 100, 2)
            : 0.0;

        return [
            'marksData' => $marksData,
            'totalObtained' => $totalObtained,
            'totalFull' => $totalFull,
            'percentage' => $percentage,
            'allPassed' => $marksData->isNotEmpty() && $marksData->every(fn (array $row) => $row['passed']),
            'division' => $this->gradeLabel($percentage),
        ];
    }

    public function getMarkDisplayMetrics(Mark $mark): array
    {
        $obtained = (float) $mark->total_marks;
        $full = $this->fullMarksForMark($mark);
        $pass = $this->passMarksForMark($mark);
        $percentage = $this->markPercentage($mark);

        return [
            'obtained_marks' => $obtained,
            'full_marks' => $full,
            'pass_marks' => $pass,
            'percentage' => $percentage,
            'grade_label' => $this->gradeLabel($percentage),
            'passed' => $mark->is_passed,
        ];
    }

    public function getAttendanceSummary(
        Student $student,
        ?CarbonInterface $startDate = null,
        ?CarbonInterface $endDate = null,
        ?int $subjectId = null
    ): array {
        $query = $student->attendances();

        if ($startDate || $endDate || $subjectId) {
            $query->whereHas('attendanceSession', function ($sessionQuery) use ($startDate, $endDate, $subjectId) {
                if ($startDate && $endDate) {
                    $sessionQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                } elseif ($startDate) {
                    $sessionQuery->whereDate('date', '>=', $startDate->toDateString());
                } elseif ($endDate) {
                    $sessionQuery->whereDate('date', '<=', $endDate->toDateString());
                }

                if ($subjectId) {
                    $sessionQuery->where('subject_id', $subjectId);
                }
            });
        }

        return $this->summarizeAttendance($query->get(['status']));
    }

    public function getAttendanceRecords(
        Student $student,
        ?CarbonInterface $startDate = null,
        ?CarbonInterface $endDate = null,
        ?int $subjectId = null,
        ?int $semester = null
    ): Collection {
        $query = $student->attendances()
            ->with(['attendanceSession.subject', 'attendanceSession.teacher.user']);

        $query->whereHas('attendanceSession', function ($sessionQuery) use ($startDate, $endDate, $subjectId, $semester) {
            if ($startDate && $endDate) {
                $sessionQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            } elseif ($startDate) {
                $sessionQuery->whereDate('date', '>=', $startDate->toDateString());
            } elseif ($endDate) {
                $sessionQuery->whereDate('date', '<=', $endDate->toDateString());
            }

            if ($subjectId) {
                $sessionQuery->where('subject_id', $subjectId);
            }

            // Filter by semester via subject
            if ($semester !== null) {
                $sessionQuery->whereHas('subject', fn ($sq) => $sq->where('semester', $semester));
            }
        });

        return $query->get();
    }

    public function summarizeAttendance(Collection $attendances): array
    {
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
        ];
    }

    public function getAttendanceDateBounds(Student $student): array
    {
        $bounds = Attendance::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->where('attendances.student_id', $student->id)
            ->selectRaw('MIN(attendance_sessions.date) as first_date, MAX(attendance_sessions.date) as last_date')
            ->first();

        return [
            'first_date' => !empty($bounds?->first_date)
                ? Carbon::parse($bounds->first_date)->startOfDay()
                : null,
            'last_date' => !empty($bounds?->last_date)
                ? Carbon::parse($bounds->last_date)->endOfDay()
                : null,
        ];
    }

    public function getProgramSubjectOverview(Student $student, ?AcademicSession $session = null): array
    {
        $activeSession = $session ?: $student->academicSession ?: AcademicSession::current();
        $sessionId = $activeSession?->id;

        $program = $student->program()
            ->with([
                'subjects' => function ($subjectQuery) use ($sessionId) {
                    $subjectQuery
                        ->orderBy('semester')
                        ->orderBy('name')
                        ->with([
                            'teachers' => function ($teacherQuery) use ($sessionId) {
                                $teacherQuery
                                    ->select('teachers.id', 'teachers.user_id')
                                    ->with('user:id,name')
                                    ->orderBy('teachers.id');

                                if ($sessionId) {
                                    $teacherQuery->wherePivot('academic_session_id', $sessionId);
                                }
                            },
                        ]);
                },
            ])
            ->first();

        if (!$program) {
            return [
                'completed' => collect(),
                'running' => collect(),
                'upcoming' => collect(),
                'counts' => [
                    'completed' => 0,
                    'running' => 0,
                    'upcoming' => 0,
                ],
            ];
        }

        $semesterStatuses = $this->semesterProgressMap($student, $activeSession);
        $allSubjects = $program->subjects;

        $statusGroups = collect(['completed', 'running', 'upcoming'])->mapWithKeys(
            function (string $status) use ($allSubjects, $semesterStatuses, $student) {
                $semesterGroups = $allSubjects
                    ->filter(function (Subject $subject) use ($status, $semesterStatuses, $student) {
                        $subjectSemester = (int) ($subject->semester ?? 0);

                        return $this->resolveSemesterProgressStatus(
                            $subjectSemester,
                            (int) ($student->current_semester ?? 0),
                            $semesterStatuses
                        ) === $status;
                    })
                    ->groupBy(fn (Subject $subject) => (int) ($subject->semester ?? 0))
                    ->sortKeys()
                    ->map(function (Collection $semesterSubjects, int $semester) {
                        return [
                            'semester' => $semester,
                            'title' => 'Semester ' . $semester,
                            'subject_count' => $semesterSubjects->count(),
                            'subjects' => $semesterSubjects
                                ->map(fn (Subject $subject) => $this->formatProgramSubject($subject))
                                ->values()
                                ->all(),
                        ];
                    })
                    ->values();

                return [$status => $semesterGroups];
            }
        );

        return [
            'completed' => $statusGroups->get('completed', collect()),
            'running' => $statusGroups->get('running', collect()),
            'upcoming' => $statusGroups->get('upcoming', collect()),
            'counts' => [
                'completed' => $statusGroups->get('completed', collect())->sum('subject_count'),
                'running' => $statusGroups->get('running', collect())->sum('subject_count'),
                'upcoming' => $statusGroups->get('upcoming', collect())->sum('subject_count'),
            ],
        ];
    }

    private function markPercentage(Mark $mark): float
    {
        $full = $this->fullMarksForMark($mark);

        if ($full <= 0) {
            return 0.0;
        }

        return round(((float) $mark->total_marks / $full) * 100, 2);
    }

    private function fullMarksForMark(Mark $mark): float
    {
        if (($mark->exam?->category ?? null) === 'monthly_assessment') {
            return (float) ($mark->assessment_full_marks ?? 0);
        }

        $scheme = $this->markingSchemeForMark($mark);

        return (float) ($scheme['full_marks_internal_theory'] ?? 0)
            + (float) ($scheme['full_marks_external_theory'] ?? 0)
            + (float) ($scheme['full_marks_internal_practical'] ?? 0)
            + (float) ($scheme['full_marks_external_practical'] ?? 0);
    }

    private function passMarksForMark(Mark $mark): float
    {
        if (($mark->exam?->category ?? null) === 'monthly_assessment') {
            return (float) ($mark->assessment_pass_marks ?? 0);
        }

        $scheme = $this->markingSchemeForMark($mark);

        return (float) ($scheme['pass_marks_internal_theory'] ?? 0)
            + (float) ($scheme['pass_marks_external_theory'] ?? 0)
            + (float) ($scheme['pass_marks_internal_practical'] ?? 0)
            + (float) ($scheme['pass_marks_external_practical'] ?? 0);
    }

    private function markingSchemeForMark(Mark $mark): array
    {
        $cacheKey = "{$mark->exam_id}:{$mark->subject_id}";

        if (isset($this->markingSchemeCache[$cacheKey])) {
            return $this->markingSchemeCache[$cacheKey];
        }

        $scheme = DB::table('exam_subject_marking_schemes')
            ->where('exam_id', $mark->exam_id)
            ->where('subject_id', $mark->subject_id)
            ->first();

        if ($scheme) {
            return $this->markingSchemeCache[$cacheKey] = [
                'full_marks_internal_theory' => $scheme->full_marks_internal_theory,
                'pass_marks_internal_theory' => $scheme->pass_marks_internal_theory,
                'full_marks_external_theory' => $scheme->full_marks_external_theory,
                'pass_marks_external_theory' => $scheme->pass_marks_external_theory,
                'full_marks_internal_practical' => $scheme->full_marks_internal_practical,
                'pass_marks_internal_practical' => $scheme->pass_marks_internal_practical,
                'full_marks_external_practical' => $scheme->full_marks_external_practical,
                'pass_marks_external_practical' => $scheme->pass_marks_external_practical,
            ];
        }

        return $this->markingSchemeCache[$cacheKey] = [
            'full_marks_internal_theory' => $mark->subject?->full_marks_internal_theory ?? 0,
            'pass_marks_internal_theory' => $mark->subject?->pass_marks_internal_theory ?? 0,
            'full_marks_external_theory' => $mark->subject?->full_marks_external_theory ?? 0,
            'pass_marks_external_theory' => $mark->subject?->pass_marks_external_theory ?? 0,
            'full_marks_internal_practical' => $mark->subject?->full_marks_internal_practical ?? 0,
            'pass_marks_internal_practical' => $mark->subject?->pass_marks_internal_practical ?? 0,
            'full_marks_external_practical' => $mark->subject?->full_marks_external_practical ?? 0,
            'pass_marks_external_practical' => $mark->subject?->pass_marks_external_practical ?? 0,
        ];
    }

    private function gradeLabel(float $percentage): string
    {
        return match (true) {
            $percentage >= 80 => 'Distinction',
            $percentage >= 60 => 'First Division',
            $percentage >= 45 => 'Second Division',
            $percentage >= 32 => 'Third Division',
            default => 'Fail',
        };
    }

    private function incrementGradeDistribution(array &$gradeDistribution, float $percentage): void
    {
        if ($percentage >= 80) {
            $gradeDistribution['distinction']++;

            return;
        }

        if ($percentage >= 60) {
            $gradeDistribution['first_division']++;

            return;
        }

        if ($percentage >= 45) {
            $gradeDistribution['second_division']++;

            return;
        }

        if ($percentage >= 32) {
            $gradeDistribution['third_division']++;

            return;
        }

        $gradeDistribution['fail']++;
    }

    private function semesterProgressMap(Student $student, ?AcademicSession $session = null): array
    {
        $sourceSession = $session ?: $student->academicSession ?: AcademicSession::current();

        if (!$sourceSession) {
            return [];
        }

        return $sourceSession->semesters()
            ->get(['semester_number', 'status'])
            ->mapWithKeys(fn ($semester) => [(int) $semester->semester_number => (string) $semester->status])
            ->all();
    }

    private function resolveSemesterProgressStatus(int $subjectSemester, int $currentSemester, array $semesterStatuses): string
    {
        return match ($semesterStatuses[$subjectSemester] ?? null) {
            'completed' => 'completed',
            'running', 'delayed' => 'running',
            'upcoming' => 'upcoming',
            default => $subjectSemester < $currentSemester
                ? 'completed'
                : ($subjectSemester === $currentSemester ? 'running' : 'upcoming'),
        };
    }

    private function formatProgramSubject(Subject $subject): array
    {
        $teachers = $subject->teachers
            ->filter(fn ($teacher) => filled($teacher->user?->name))
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'role' => $this->formatTeacherRole($teacher->pivot?->role),
                    'section' => filled($teacher->pivot?->section) ? (string) $teacher->pivot->section : null,
                ];
            })
            ->unique(fn (array $teacher) => $teacher['name'] . '|' . ($teacher['role'] ?? '') . '|' . ($teacher['section'] ?? ''))
            ->values();

        $teacherSummary = $teachers->isNotEmpty()
            ? $teachers
                ->map(function (array $teacher) {
                    $details = collect([
                        $teacher['role'] ?? null,
                        filled($teacher['section'] ?? null) ? 'Section ' . $teacher['section'] : null,
                    ])->filter()->implode(', ');

                    return $details !== ''
                        ? $teacher['name'] . ' (' . $details . ')'
                        : $teacher['name'];
                })
                ->implode(', ')
            : 'Teacher not assigned';

        $passTheoryMarks = (int) (($subject->pass_marks_internal_theory ?? 0) + ($subject->pass_marks_external_theory ?? 0));
        $passPracticalMarks = (int) (($subject->pass_marks_internal_practical ?? 0) + ($subject->pass_marks_external_practical ?? 0));

        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'code' => $subject->code,
            'semester' => (int) ($subject->semester ?? 0),
            'type' => (string) ($subject->type ?: 'theory'),
            'type_label' => (string) Str::of((string) ($subject->type ?: 'theory'))->replace('_', ' ')->headline(),
            'credit_hours' => $subject->credit_hours,
            'details' => filled($subject->details) ? (string) $subject->details : null,
            'syllabus' => filled($subject->syllabus) ? (string) $subject->syllabus : null,
            'syllabus_name' => filled($subject->syllabus) ? basename((string) $subject->syllabus) : null,
            'syllabus_url' => $subject->syllabus_url,
            'is_active' => (bool) $subject->is_active,
            'status_label' => $subject->is_active ? 'Active' : 'Inactive',
            'has_theory' => $subject->hasTheory(),
            'has_practical' => $subject->hasPractical(),
            'full_marks_internal_theory' => (int) ($subject->full_marks_internal_theory ?? 0),
            'pass_marks_internal_theory' => (int) ($subject->pass_marks_internal_theory ?? 0),
            'full_marks_external_theory' => (int) ($subject->full_marks_external_theory ?? 0),
            'pass_marks_external_theory' => (int) ($subject->pass_marks_external_theory ?? 0),
            'full_marks_internal_practical' => (int) ($subject->full_marks_internal_practical ?? 0),
            'pass_marks_internal_practical' => (int) ($subject->pass_marks_internal_practical ?? 0),
            'full_marks_external_practical' => (int) ($subject->full_marks_external_practical ?? 0),
            'pass_marks_external_practical' => (int) ($subject->pass_marks_external_practical ?? 0),
            'total_theory_marks' => (int) $subject->total_theory_marks,
            'total_theory_pass_marks' => $passTheoryMarks,
            'total_practical_marks' => (int) $subject->total_practical_marks,
            'total_practical_pass_marks' => $passPracticalMarks,
            'total_full_marks' => (int) $subject->total_full_marks,
            'total_pass_marks' => (int) $subject->total_pass_marks,
            'teacher_summary' => $teacherSummary,
            'teachers' => $teachers->all(),
        ];
    }

    private function formatTeacherRole(?string $role): ?string
    {
        $normalizedRole = Str::of((string) $role)
            ->replace(['_', '-'], ' ')
            ->squish()
            ->lower();

        if ($normalizedRole->isEmpty() || (string) $normalizedRole === 'teacher') {
            return null;
        }

        return (string) $normalizedRole->headline();
    }
}
