<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            ->filter(fn (Mark $mark) => $mark->exam?->is_published)
            ->values();

        $assessmentResults = $this->buildAssessmentResults($marks);
        $totalObtained = 0.0;
        $totalFull = 0.0;

        $gradeDistribution = [
            'distinction' => 0,
            'first_division' => 0,
            'second_division' => 0,
            'third_division' => 0,
            'fail' => 0,
        ];

        foreach ($marks as $mark) {
            $displayMetrics = $this->getMarkDisplayMetrics($mark);
            $percentage = $displayMetrics['percentage'];

            $totalObtained += $displayMetrics['obtained_marks'];
            $totalFull += $displayMetrics['full_marks'];

            if ($percentage >= 80) {
                $gradeDistribution['distinction']++;
            } elseif ($percentage >= 60) {
                $gradeDistribution['first_division']++;
            } elseif ($percentage >= 45) {
                $gradeDistribution['second_division']++;
            } elseif ($percentage >= 32) {
                $gradeDistribution['third_division']++;
            } else {
                $gradeDistribution['fail']++;
            }
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
            ->filter(fn (Mark $mark) => $mark->exam?->is_published)
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
        ?int $subjectId = null
    ): Collection {
        $query = $student->attendances()
            ->with(['attendanceSession.subject', 'attendanceSession.teacher.user']);

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
}
