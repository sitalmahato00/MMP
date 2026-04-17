<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportTypes = [
            'attendance' => 'Attendance Report',
            'marks' => 'Marks / Grading Report',
            'admissions' => 'Admissions Report',
            'students' => 'Student List Report',
        ];

        $selectedType = $this->resolveType($request->string('report_type')->toString(), array_keys($reportTypes));
        $generated = $request->boolean('generated');

        $filters = $this->resolveFilters($request);

        $paginator = null;

        if ($generated) {
            $paginator = $this->buildPaginator($selectedType, $filters, max(1, $request->integer('page', 1)), 20);
            $paginator->appends($request->query());
        }

        return view('admin.reports.index', [
            'reportTypes' => $reportTypes,
            'selectedType' => $selectedType,
            'generated' => $generated,
            'rows' => $paginator,
            'sessions' => AcademicSession::query()->orderByDesc('start_date')->get(['id', 'name', 'name_bs']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'code']),
            'programs' => Program::query()->with('department:id,name')->orderBy('name')->get(['id', 'name', 'code', 'department_id']),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'csv', 'excel'], true), 404);

        $reportTypes = ['attendance', 'marks', 'admissions', 'students'];
        $selectedType = $this->resolveType($request->string('report_type')->toString(), $reportTypes);

        $filters = $this->resolveFilters($request);

        $rows = $this->buildRows($selectedType, $filters);
        $title = match ($selectedType) {
            'marks' => 'Marks / Grading Report',
            'admissions' => 'Admissions Report',
            'students' => 'Student List Report',
            default => 'Attendance Report',
        };

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf', [
                'title' => $title,
                'rows' => $rows,
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape');

            return $pdf->download($this->filename($selectedType, 'pdf'));
        }

        if ($format === 'excel') {
            return $this->exportDelimited($rows, "\t", $this->filename($selectedType, 'xls'), 'application/vnd.ms-excel');
        }

        return $this->exportDelimited($rows, ',', $this->filename($selectedType, 'csv'), 'text/csv');
    }

    private function buildPaginator(string $selectedType, array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        if ($selectedType === 'admissions') {
            $query = Application::query()
                ->with('department:id,name,code')
                ->when($filters['department_id'], fn ($q) => $q->where('department_id', $filters['department_id']))
                ->when($filters['date_from'], fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
                ->when($filters['date_to'], fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
                ->orderByDesc('created_at');

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);
            $paginator->setCollection($paginator->getCollection()->map(function (Application $application) {
                return [
                    'student_name' => $application->full_name,
                    'attendance' => '-',
                    'marks_grade' => $application->gpa ? 'GPA ' . $application->gpa : '-',
                    'status' => ucfirst($application->status ?? 'pending'),
                ];
            }));

            return $paginator;
        }

        $query = Student::query()
            ->with('user:id,name', 'program:id,name,code', 'department:id,name,code')
            ->when($filters['academic_session_id'], fn ($q) => $q->where('academic_session_id', $filters['academic_session_id']))
            ->when($filters['department_id'], fn ($q) => $q->where('department_id', $filters['department_id']))
            ->when($filters['program_id'], fn ($q) => $q->where('program_id', $filters['program_id']))
            ->orderBy('roll_number')
            ->orderBy('id');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $metrics = $this->buildStudentMetrics($paginator->getCollection(), $filters);

        $paginator->setCollection($paginator->getCollection()->map(function (Student $student) use ($metrics, $selectedType) {
            $studentMetric = $metrics[$student->id] ?? [
                'attendance' => null,
                'score' => null,
                'grade' => '-',
                'status' => ucfirst($student->status ?? 'active'),
            ];

            $marksGrade = $selectedType === 'marks'
                ? ($studentMetric['grade'] !== '-' ? $studentMetric['grade'] . ' (' . number_format((float) $studentMetric['score'], 1) . '%)' : '-')
                : ($studentMetric['score'] !== null ? number_format((float) $studentMetric['score'], 1) . '%' : '-');

            return [
                'student_name' => $student->user?->name ?? 'Student',
                'attendance' => $studentMetric['attendance'] !== null ? number_format((float) $studentMetric['attendance'], 1) . '%' : '-',
                'marks_grade' => $marksGrade,
                'status' => $studentMetric['status'],
            ];
        }));

        return $paginator;
    }

    private function buildRows(string $selectedType, array $filters): Collection
    {
        return $this->buildPaginator($selectedType, $filters, 1, 5000)->getCollection()->values();
    }

    private function buildStudentMetrics(Collection $students, array $filters): array
    {
        if ($students->isEmpty()) {
            return [];
        }

        $studentIds = $students->pluck('id')->all();

        $attendanceQuery = Attendance::query()
            ->whereIn('student_id', $studentIds)
            ->when($filters['academic_session_id'], function ($q) use ($filters) {
                $q->whereHas('attendanceSession', fn ($sq) => $sq->where('academic_session_id', $filters['academic_session_id']));
            })
            ->when($filters['date_from'], function ($q) use ($filters) {
                $q->whereHas('attendanceSession', fn ($sq) => $sq->whereDate('date', '>=', $filters['date_from']));
            })
            ->when($filters['date_to'], function ($q) use ($filters) {
                $q->whereHas('attendanceSession', fn ($sq) => $sq->whereDate('date', '<=', $filters['date_to']));
            })
            ->get(['student_id', 'status']);

        $attendanceByStudent = $attendanceQuery
            ->groupBy('student_id')
            ->map(function (Collection $items) {
                $total = $items->count();
                if ($total === 0) {
                    return null;
                }

                return ($items->where('status', 'present')->count() / $total) * 100;
            });

        $marks = Mark::query()
            ->published()
            ->with('subject:id,full_marks_internal_theory,full_marks_external_theory,full_marks_internal_practical,full_marks_external_practical')
            ->whereIn('student_id', $studentIds)
            ->when($filters['academic_session_id'], function ($q) use ($filters) {
                $q->whereHas('exam', fn ($sq) => $sq->where('academic_session_id', $filters['academic_session_id']));
            })
            ->when($filters['date_from'], function ($q) use ($filters) {
                $q->whereHas('exam', fn ($sq) => $sq->whereDate('start_date', '>=', $filters['date_from']));
            })
            ->when($filters['date_to'], function ($q) use ($filters) {
                $q->whereHas('exam', fn ($sq) => $sq->whereDate('start_date', '<=', $filters['date_to']));
            })
            ->get();

        $marksByStudent = $marks->groupBy('student_id')->map(function (Collection $rows) {
            $scores = $rows
                ->map(fn (Mark $mark) => $this->markPercentage($mark))
                ->filter(fn ($value) => $value !== null)
                ->values();

            $score = $scores->isNotEmpty() ? (float) $scores->avg() : null;

            return [
                'score' => $score,
                'grade' => $this->grade($score),
                'status' => $score === null ? 'Needs Review' : ($score >= 45 ? 'Pass' : 'At Risk'),
            ];
        });

        $result = [];
        foreach ($students as $student) {
            $attendance = $attendanceByStudent[$student->id] ?? null;
            $mark = $marksByStudent[$student->id] ?? null;
            $result[$student->id] = [
                'attendance' => $attendance,
                'score' => $mark['score'] ?? null,
                'grade' => $mark['grade'] ?? '-',
                'status' => $mark['status'] ?? ucfirst($student->status ?? 'active'),
            ];
        }

        return $result;
    }

    private function markPercentage(Mark $mark): ?float
    {
        if ($mark->is_absent || $mark->is_withheld || !$mark->subject) {
            return null;
        }

        $fullMarks = (float) ($mark->subject->full_marks_internal_theory ?? 0)
            + (float) ($mark->subject->full_marks_external_theory ?? 0)
            + (float) ($mark->subject->full_marks_internal_practical ?? 0)
            + (float) ($mark->subject->full_marks_external_practical ?? 0);

        if ($fullMarks <= 0) {
            return null;
        }

        return ((float) $mark->total_marks / $fullMarks) * 100;
    }

    private function grade(?float $score): string
    {
        if ($score === null) {
            return '-';
        }

        if ($score >= 80) {
            return 'A';
        }

        if ($score >= 60) {
            return 'B';
        }

        if ($score >= 45) {
            return 'C';
        }

        return 'D';
    }

    private function resolveType(string $requestedType, array $allowed): string
    {
        return in_array($requestedType, $allowed, true) ? $requestedType : 'attendance';
    }

    private function filename(string $type, string $extension): string
    {
        $timestamp = preg_replace('/[^0-9A-Za-z_]/', '', bsDate(Carbon::now(), 'Ymd_His'));
        if ($timestamp === '') {
            $timestamp = Carbon::now()->format('Ymd_His');
        }

        return 'report_' . $type . '_' . $timestamp . '.' . $extension;
    }

    private function resolveFilters(Request $request): array
    {
        $dateFromBs = $request->string('date_from')->trim()->toString();
        $dateToBs = $request->string('date_to')->trim()->toString();

        $dateFromBs = $dateFromBs !== '' ? $dateFromBs : null;
        $dateToBs = $dateToBs !== '' ? $dateToBs : null;

        return [
            'academic_session_id' => $request->integer('academic_session_id') ?: null,
            'department_id' => $request->integer('department_id') ?: null,
            'program_id' => $request->integer('program_id') ?: null,
            'date_from_bs' => $dateFromBs,
            'date_to_bs' => $dateToBs,
            'date_from' => $dateFromBs ? adDate($dateFromBs) : null,
            'date_to' => $dateToBs ? adDate($dateToBs) : null,
        ];
    }

    private function exportDelimited(Collection $rows, string $delimiter, string $filename, string $contentType): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $delimiter) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Name', 'Attendance %', 'Marks / Grade', 'Status'], $delimiter);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['student_name'] ?? '-',
                    $row['attendance'] ?? '-',
                    $row['marks_grade'] ?? '-',
                    $row['status'] ?? '-',
                ], $delimiter);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => $contentType,
        ]);
    }
}
