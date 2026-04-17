<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $metric = $this->resolveMetric($request->string('metric')->toString());

        $metricState = Cache::remember($this->metricCacheKey($filters, $metric), 300, function () use ($filters, $metric) {
            return $this->buildAnalyticsState($filters, $metric);
        });

        $detailState = null;
        $detailRequested = $request->boolean('detail');
        $detailPage = max(1, $request->integer('detail_page', 1));

        if ($detailRequested) {
            $detailState = Cache::remember($this->detailCacheKey($filters, $detailPage), 180, function () use ($filters, $detailPage) {
                return $this->buildDetailState($filters, $detailPage);
            });
        }

        $state = array_merge($metricState, [
            'filters' => [
                'sessionId' => $filters['selectedSession']?->id,
                'departmentId' => $filters['selectedDepartment']?->id,
                'programId' => $filters['selectedProgram']?->id,
                'metric' => $metric,
                'detail' => $detailRequested,
                'detailPage' => $detailPage,
            ],
            'detail' => $detailState,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($state);
        }

        return view('admin.analytics-modern', [
            'analyticsState' => $state,
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $sessionOptions = Cache::remember('admin_analytics_sessions', 900, function () {
            return AcademicSession::query()
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->get(['id', 'name', 'name_bs', 'start_date', 'end_date', 'is_active', 'status']);
        });

        $departmentOptions = Cache::remember('admin_analytics_departments', 900, function () {
            return Department::active()
                ->withCount('students')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'slug', 'seat_capacity']);
        });

        $programOptions = Cache::remember('admin_analytics_programs', 900, function () {
            return Program::active()
                ->with('department')
                ->withCount('students')
                ->orderBy('name')
                ->get(['id', 'department_id', 'name', 'code', 'slug', 'total_semesters', 'duration_years']);
        });

        $fallbackSession = AcademicSession::current() ?: $sessionOptions->first();
        $selectedSession = $this->resolveSession($request->integer('session_id'), $sessionOptions, $fallbackSession);

        $selectedDepartment = $request->filled('department_id')
            ? $departmentOptions->firstWhere('id', $request->integer('department_id'))
            : null;

        $selectedProgram = $request->filled('program_id')
            ? $programOptions->firstWhere('id', $request->integer('program_id'))
            : null;

        return [
            'sessionOptions' => $sessionOptions,
            'departmentOptions' => $departmentOptions,
            'programOptions' => $programOptions,
            'selectedSession' => $selectedSession,
            'selectedDepartment' => $selectedDepartment,
            'selectedProgram' => $selectedProgram,
        ];
    }

    private function resolveMetric(string $metric): string
    {
        return in_array($metric, ['attendance', 'results', 'admissions'], true) ? $metric : 'attendance';
    }

    private function metricDefinitions(): array
    {
        return [
            'attendance' => [
                'label' => 'Attendance',
                'description' => 'Track present rate across the selected session.',
                'tone' => 'red',
            ],
            'results' => [
                'label' => 'Results',
                'description' => 'Compare marks across completed exams.',
                'tone' => 'slate',
            ],
            'admissions' => [
                'label' => 'Admissions',
                'description' => 'Explore application flow and status mix.',
                'tone' => 'amber',
            ],
        ];
    }

    private function metricCacheKey(array $filters, string $metric): string
    {
        return sprintf(
            'admin_analytics_metric:%s:%s:%s:%s',
            $metric,
            $filters['selectedSession']?->id ?? 'none',
            $filters['selectedDepartment']?->id ?? 'none',
            $filters['selectedProgram']?->id ?? 'none'
        );
    }

    private function buildAnalyticsState(array $filters, string $metric): array
    {
        $selectedSession = $filters['selectedSession'];
        $selectedDepartment = $filters['selectedDepartment'];
        $selectedProgram = $filters['selectedProgram'];
        $definitions = $this->metricDefinitions();
        $selectedDefinition = $definitions[$metric] ?? $definitions['attendance'];
        $reportQuery = $this->buildStudentReportQuery($selectedSession, $selectedDepartment, $selectedProgram);

        $sharedState = [
            'metricOptions' => array_values(array_map(function (string $key, array $definition) use ($metric) {
                return [
                    'key' => $key,
                    'label' => $definition['label'],
                    'description' => $definition['description'],
                    'tone' => $definition['tone'],
                    'selected' => $metric === $key,
                ];
            }, array_keys($definitions), $definitions)),
            'selectedMetric' => $metric,
            'selectedMetricLabel' => $selectedDefinition['label'],
            'selectedMetricDescription' => $selectedDefinition['description'],
            'selectedMetricTone' => $selectedDefinition['tone'],
            'selectedSessionLabel' => $selectedSession?->name ?? 'Current session',
            'selectedDepartmentLabel' => $selectedDepartment?->name,
            'selectedProgramLabel' => $selectedProgram?->name,
            'sessionOptions' => $filters['sessionOptions']->map(fn (AcademicSession $session) => [
                'id' => $session->id,
                'label' => trim($session->name . ($session->name_bs ? ' / ' . $session->name_bs : '')),
                'selected' => $selectedSession?->id === $session->id,
            ])->values()->all(),
            'departmentOptions' => $filters['departmentOptions']->map(fn (Department $department) => [
                'id' => $department->id,
                'label' => trim(($department->code ? $department->code . ' - ' : '') . $department->name),
                'selected' => $selectedDepartment?->id === $department->id,
                'count' => (int) ($department->students_count ?? 0),
            ])->values()->all(),
            'programOptions' => $filters['programOptions']->map(fn (Program $program) => [
                'id' => $program->id,
                'label' => trim(($program->code ? $program->code . ' - ' : '') . $program->name),
                'department' => $program->department?->name,
                'selected' => $selectedProgram?->id === $program->id,
            ])->values()->all(),
            'reportHref' => route('admin.students.index', $reportQuery),
            'updatedAt' => now()->toIso8601String(),
        ];

        $metricState = match ($metric) {
            'results' => $this->buildResultsMetricState($filters),
            'admissions' => $this->buildAdmissionsMetricState($filters),
            default => $this->buildAttendanceMetricState($filters),
        };

        return array_merge($sharedState, $metricState);
    }

    private function buildAttendanceMetricState(array $filters): array
    {
        $selectedSession = $filters['selectedSession'];
        $selectedDepartment = $filters['selectedDepartment'];
        $selectedProgram = $filters['selectedProgram'];
        $window = $this->resolveWindow($selectedSession);
        $attendanceRecords = $this->loadAttendanceRecords($selectedSession, $selectedDepartment, $selectedProgram);
        $summary = $this->summarizeAttendance($attendanceRecords);
        $trend = $this->buildAttendanceTrend($attendanceRecords, $window);
        $comparisonRows = $this->buildAttendanceComparisonRows($attendanceRecords);
        $comparisonValues = $comparisonRows->take(6)->values();
        $trendChange = $this->buildSeriesTrend($trend['values'] ?? []);
        $bestDepartment = $comparisonRows->first(fn (array $row) => !empty($row['has_data']));
        $lowestDepartment = $comparisonRows->filter(fn (array $row) => !empty($row['has_data']))->sortBy('attendance_rate')->first();

        return [
            'mainChart' => [
                'title' => 'Attendance trend',
                'subtitle' => $window['label'] . ' · present rate over time',
                'type' => 'line',
                'unit' => '%',
                'labels' => $trend['labels'],
                'datasets' => [[
                    'label' => 'Attendance %',
                    'data' => $trend['values'],
                    'borderColor' => '#8B0000',
                    'backgroundColor' => 'rgba(139, 0, 0, 0.18)',
                    'fill' => true,
                    'tension' => 0.38,
                    'pointBackgroundColor' => '#8B0000',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 3,
                ]],
                'yMax' => 100,
                'emptyMessage' => 'No attendance records were found for this selection.',
            ],
            'comparisonChart' => [
                'title' => 'Department comparison',
                'subtitle' => 'Highest attendance rates in the current view',
                'type' => 'bar',
                'indexAxis' => 'y',
                'unit' => '%',
                'labels' => $comparisonValues->pluck('label')->all(),
                'datasets' => [[
                    'label' => 'Attendance %',
                    'data' => $comparisonValues->pluck('attendance_rate')->all(),
                    'backgroundColor' => 'rgba(139, 0, 0, 0.86)',
                    'borderSkipped' => false,
                    'borderRadius' => 12,
                    'maxBarThickness' => 24,
                ]],
                'yMax' => 100,
                'emptyMessage' => 'No department comparison is available yet.',
            ],
            'insights' => [
                [
                    'tone' => 'info',
                    'title' => 'Current average',
                    'message' => sprintf('Attendance is %s%% across %s records.', number_format($summary['rate'], 1), number_format($summary['total'])),
                ],
                [
                    'tone' => 'success',
                    'title' => 'Trend movement',
                    'message' => sprintf('The selected period is %s from the first bucket.', $trendChange['text']),
                ],
                [
                    'tone' => 'warning',
                    'title' => 'Best department',
                    'message' => $bestDepartment
                        ? sprintf('%s leads with %s%% attendance.', $bestDepartment['name'], number_format($bestDepartment['attendance_rate'], 1))
                        : 'No department ranking is available yet.',
                ],
                [
                    'tone' => 'info',
                    'title' => 'Lowest department',
                    'message' => $lowestDepartment
                        ? sprintf('%s is at %s%% and needs attention.', $lowestDepartment['name'], number_format($lowestDepartment['attendance_rate'], 1))
                        : 'There is not enough attendance data to identify a low point.',
                ],
            ],
        ];
    }

    private function buildResultsMetricState(array $filters): array
    {
        $selectedSession = $filters['selectedSession'];
        $selectedDepartment = $filters['selectedDepartment'];
        $selectedProgram = $filters['selectedProgram'];
        $marks = $this->loadMarks($selectedSession, $selectedDepartment, $selectedProgram);
        $summary = $this->summarizeMarks($marks);

        $examRows = $marks
            ->groupBy(fn (Mark $mark) => $mark->exam_id)
            ->map(function (Collection $examMarks) {
                $exam = $examMarks->first()?->exam;
                $percentages = $examMarks
                    ->map(fn (Mark $mark) => $this->calculateMarkPercentage($mark))
                    ->filter(fn ($value) => $value !== null)
                    ->values();

                return [
                    'sort' => $exam?->start_date?->timestamp ?? $exam?->created_at?->timestamp ?? 0,
                    'label' => $exam?->name ?? 'Exam',
                    'score' => $percentages->isNotEmpty() ? round($percentages->avg(), 1) : 0,
                    'pass_rate' => $examMarks->count() > 0 ? round(($examMarks->filter(fn (Mark $mark) => $mark->is_passed)->count() / $examMarks->count()) * 100, 1) : 0,
                ];
            })
            ->sortBy('sort')
            ->values();

        $comparisonRows = $this->buildResultComparisonRows($marks);
        $comparisonValues = $comparisonRows->take(6)->values();
        $trendChange = $this->buildSeriesTrend($examRows->pluck('score')->all());
        $topDepartment = $comparisonRows->first(fn (array $row) => !empty($row['has_data']));
        $topExam = $examRows->isNotEmpty() ? $examRows->sortByDesc('score')->first() : null;

        return [
            'mainChart' => [
                'title' => 'Results by exam',
                'subtitle' => 'Average marks across published exams',
                'type' => 'line',
                'unit' => '%',
                'labels' => $examRows->pluck('label')->all(),
                'datasets' => [[
                    'label' => 'Average marks %',
                    'data' => $examRows->pluck('score')->all(),
                    'borderColor' => '#334155',
                    'backgroundColor' => 'rgba(51, 65, 85, 0.14)',
                    'fill' => true,
                    'tension' => 0.38,
                    'pointBackgroundColor' => '#334155',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 3,
                ]],
                'yMax' => 100,
                'emptyMessage' => 'No published marks are available for this selection.',
            ],
            'comparisonChart' => [
                'title' => 'Department result comparison',
                'subtitle' => 'Highest average marks in the current view',
                'type' => 'bar',
                'indexAxis' => 'y',
                'unit' => '%',
                'labels' => $comparisonValues->pluck('label')->all(),
                'datasets' => [[
                    'label' => 'Average marks %',
                    'data' => $comparisonValues->pluck('average_marks')->all(),
                    'backgroundColor' => 'rgba(51, 65, 85, 0.84)',
                    'borderSkipped' => false,
                    'borderRadius' => 12,
                    'maxBarThickness' => 24,
                ]],
                'yMax' => 100,
                'emptyMessage' => 'No department comparison is available yet.',
            ],
            'insights' => [
                [
                    'tone' => 'info',
                    'title' => 'Average marks',
                    'message' => sprintf('Published marks average %s%% across %s records.', number_format($summary['average_score'], 1), number_format($summary['total'])),
                ],
                [
                    'tone' => 'success',
                    'title' => 'Trend movement',
                    'message' => sprintf('Exam averages are %s from the first to the last plotted exam.', $trendChange['text']),
                ],
                [
                    'tone' => 'warning',
                    'title' => 'Best department',
                    'message' => $topDepartment
                        ? sprintf('%s leads with %s%% average marks.', $topDepartment['name'], number_format($topDepartment['average_marks'], 1))
                        : 'No department ranking is available yet.',
                ],
                [
                    'tone' => 'info',
                    'title' => 'Top exam',
                    'message' => $topExam
                        ? sprintf('%s is the strongest exam at %s%%.', $topExam['label'], number_format($topExam['score'], 1))
                        : 'No exam trend has been built yet.',
                ],
            ],
        ];
    }

    private function buildAdmissionsMetricState(array $filters): array
    {
        $selectedSession = $filters['selectedSession'];
        $selectedDepartment = $filters['selectedDepartment'];
        $selectedProgram = $filters['selectedProgram'];
        $window = $this->resolveWindow($selectedSession);
        $applications = $this->loadApplications($window, $selectedDepartment, $selectedProgram);
        $totalApplications = $applications->count();
        $acceptedApplications = $applications->where('status', 'accepted')->count();
        $acceptedRate = $totalApplications > 0 ? round(($acceptedApplications / $totalApplications) * 100, 1) : 0.0;

        $trend = $this->buildApplicationTrend($applications, $window);
        $comparisonRows = $this->buildApplicationStatusComparison($applications);
        $comparisonValues = $comparisonRows->values();
        $trendChange = $this->buildSeriesTrend($trend['values'] ?? []);
        $departmentRows = $this->buildApplicationDepartmentRows($applications);
        $topDepartment = $departmentRows->first(fn (array $row) => !empty($row['has_data']));

        $insights = [
            [
                'tone' => 'info',
                'title' => 'Applications received',
                'message' => sprintf('%s applications were submitted in the current window.', number_format($totalApplications)),
            ],
            [
                'tone' => 'success',
                'title' => 'Acceptance rate',
                'message' => sprintf('%s applications are accepted, which is %s%% of the current set.', number_format($acceptedApplications), number_format($acceptedRate, 1)),
            ],
            [
                'tone' => 'warning',
                'title' => 'Most active department',
                'message' => $topDepartment
                    ? sprintf('%s leads with %s applications.', $topDepartment['name'], number_format($topDepartment['applications']))
                    : 'No department ranking is available yet.',
            ],
            [
                'tone' => 'info',
                'title' => 'Trend movement',
                'message' => sprintf('Application volume is %s across the selected buckets.', $trendChange['text']),
            ],
        ];

        if ($selectedProgram) {
            $insights[] = [
                'tone' => 'info',
                'title' => 'Program filter note',
                'message' => 'Applications are stored by department, so the selected program is mapped to its department for this view.',
            ];
        }

        return [
            'mainChart' => [
                'title' => 'Application flow',
                'subtitle' => $window['label'] . ' · application volume over time',
                'type' => 'line',
                'unit' => 'applications',
                'labels' => $trend['labels'],
                'datasets' => [[
                    'label' => 'Applications',
                    'data' => $trend['values'],
                    'borderColor' => '#B45309',
                    'backgroundColor' => 'rgba(180, 83, 9, 0.16)',
                    'fill' => true,
                    'tension' => 0.38,
                    'pointBackgroundColor' => '#B45309',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 3,
                ]],
                'yMax' => null,
                'emptyMessage' => 'No applications were submitted for the selected window.',
            ],
            'comparisonChart' => [
                'title' => 'Application status mix',
                'subtitle' => 'Pending, reviewed, contacted, accepted, and rejected applications',
                'type' => 'bar',
                'indexAxis' => 'x',
                'unit' => 'applications',
                'labels' => $comparisonValues->pluck('label')->all(),
                'datasets' => [[
                    'label' => 'Applications',
                    'data' => $comparisonValues->pluck('count')->all(),
                    'backgroundColor' => [
                        'rgba(148, 163, 184, 0.82)',
                        'rgba(59, 130, 246, 0.82)',
                        'rgba(6, 182, 212, 0.82)',
                        'rgba(34, 197, 94, 0.82)',
                        'rgba(239, 68, 68, 0.82)',
                    ],
                    'borderSkipped' => false,
                    'borderRadius' => 12,
                    'maxBarThickness' => 28,
                ]],
                'yMax' => null,
                'emptyMessage' => 'No application status data is available yet.',
            ],
            'insights' => $insights,
        ];
    }

    private function buildAttendanceComparisonRows(Collection $attendanceRecords): Collection
    {
        $departments = Department::active()->withCount('students')->get();

        return $departments
            ->map(function (Department $department) use ($attendanceRecords) {
                $departmentAttendance = $attendanceRecords->filter(fn (Attendance $attendance) => (int) data_get($attendance, 'student.department_id') === (int) $department->id);
                $summary = $this->summarizeAttendance($departmentAttendance);

                return [
                    'department_id' => $department->id,
                    'name' => $department->name,
                    'label' => $department->code ?: Str::limit($department->name, 16),
                    'students' => (int) ($department->students_count ?? 0),
                    'attendance_rate' => $summary['rate'],
                    'present' => $summary['present'],
                    'total' => $summary['total'],
                    'has_data' => $departmentAttendance->isNotEmpty(),
                ];
            })
            ->sortByDesc('attendance_rate')
            ->values();
    }

    private function buildResultComparisonRows(Collection $marks): Collection
    {
        $departments = Department::active()->withCount('students')->get();

        return $departments
            ->map(function (Department $department) use ($marks) {
                $departmentMarks = $marks->filter(fn (Mark $mark) => (int) data_get($mark, 'student.department_id') === (int) $department->id);
                $summary = $this->summarizeMarks($departmentMarks);

                return [
                    'department_id' => $department->id,
                    'name' => $department->name,
                    'label' => $department->code ?: Str::limit($department->name, 16),
                    'students' => (int) ($department->students_count ?? 0),
                    'average_marks' => $summary['average_score'],
                    'pass_rate' => $summary['pass_rate'],
                    'has_data' => $departmentMarks->isNotEmpty(),
                ];
            })
            ->sortByDesc('average_marks')
            ->values();
    }

    private function buildApplicationDepartmentRows(Collection $applications): Collection
    {
        $departments = Department::active()->withCount('students')->get();

        return $departments
            ->map(function (Department $department) use ($applications) {
                $departmentApplications = $applications->filter(fn (Application $application) => (int) $application->department_id === (int) $department->id);

                return [
                    'department_id' => $department->id,
                    'name' => $department->name,
                    'label' => $department->code ?: Str::limit($department->name, 16),
                    'applications' => $departmentApplications->count(),
                    'accepted' => $departmentApplications->where('status', 'accepted')->count(),
                    'accepted_rate' => $departmentApplications->count() > 0 ? round(($departmentApplications->where('status', 'accepted')->count() / $departmentApplications->count()) * 100, 1) : 0.0,
                    'has_data' => $departmentApplications->isNotEmpty(),
                ];
            })
            ->sortByDesc('applications')
            ->values();
    }

    private function buildApplicationStatusComparison(Collection $applications): Collection
    {
        $orderedStatuses = ['pending', 'reviewed', 'contacted', 'accepted', 'rejected'];

        return collect($orderedStatuses)->map(function (string $status) use ($applications) {
            return [
                'status' => $status,
                'label' => Str::headline($status),
                'count' => $applications->where('status', $status)->count(),
            ];
        });
    }

    private function buildApplicationTrend(Collection $applications, array $window): array
    {
        $buckets = $this->makeBuckets($window['start'], $window['end'], $window['bucketType']);

        foreach ($applications as $application) {
            if (!$application->created_at) {
                continue;
            }

            $date = Carbon::parse($application->created_at);
            $bucketKey = $window['bucketType'] === 'month' ? $date->format('Y-m') : $date->format('Y-m-d');

            if (!isset($buckets[$bucketKey])) {
                continue;
            }

            $buckets[$bucketKey]['total']++;
        }

        return [
            'labels' => array_values(array_map(static fn (array $bucket) => $bucket['label'], $buckets)),
            'values' => array_values(array_map(static fn (array $bucket) => $bucket['total'], $buckets)),
        ];
    }

    private function buildSeriesTrend(array $values): array
    {
        $points = array_values(array_filter($values, static fn ($value) => $value !== null));

        if (count($points) < 2) {
            return [
                'text' => '0.0%',
                'direction' => 'flat',
            ];
        }

        return $this->formatTrend($points[array_key_last($points)], $points[0]);
    }

    private function loadApplications(array $window, ?Department $department, ?Program $program): Collection
    {
        $departmentId = $department?->id ?? $program?->department_id;

        return Application::query()
            ->with('department')
            ->when($window['start'] && $window['end'], fn ($query) => $query->whereBetween('created_at', [$window['start'], $window['end']]))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->orderBy('created_at')
            ->get();
    }

    private function buildSummaryState(array $filters): array
    {
        $selectedSession = $filters['selectedSession'];
        $selectedDepartment = $filters['selectedDepartment'];
        $selectedProgram = $filters['selectedProgram'];
        $previousSession = $this->resolvePreviousSession($selectedSession);
        $sessionWindow = $this->resolveWindow($selectedSession);
        $reportQuery = $this->buildStudentReportQuery($selectedSession, $selectedDepartment, $selectedProgram);

        $currentStudents = $this->countStudents($selectedSession, $selectedDepartment, $selectedProgram);
        $previousStudents = $this->countStudents($previousSession, $selectedDepartment, $selectedProgram);

        $currentAttendanceRecords = $this->loadAttendanceRecords($selectedSession, $selectedDepartment, $selectedProgram);
        $previousAttendanceRecords = $this->loadAttendanceRecords($previousSession, $selectedDepartment, $selectedProgram);

        $currentMarks = $this->loadMarks($selectedSession, $selectedDepartment, $selectedProgram);
        $previousMarks = $this->loadMarks($previousSession, $selectedDepartment, $selectedProgram);

        $attendanceSummary = $this->summarizeAttendance($currentAttendanceRecords);
        $previousAttendanceSummary = $this->summarizeAttendance($previousAttendanceRecords);

        $marksSummary = $this->summarizeMarks($currentMarks);
        $previousMarksSummary = $this->summarizeMarks($previousMarks);

        $attendanceTrend = $this->buildAttendanceTrend($currentAttendanceRecords, $sessionWindow);
        $departmentRows = $this->buildDepartmentRows($currentAttendanceRecords, $currentMarks);
        $programRows = $this->buildProgramRows($currentAttendanceRecords, $currentMarks);
        $alerts = $this->buildAlerts($departmentRows);
        $topDepartment = $departmentRows->first();
        $topProgram = $programRows->first();

        $completedExams = Exam::query()
            ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
            ->where('status', 'completed')
            ->count();

        $totalExams = Exam::query()
            ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
            ->count();

        $summaryCards = [
            [
                'key' => 'students',
                'title' => 'Total Students',
                'value' => number_format($currentStudents),
                'suffix' => null,
                'trend' => $this->formatTrend($currentStudents, $previousStudents)['text'],
                'trendDirection' => $this->formatTrend($currentStudents, $previousStudents)['direction'],
                'note' => $selectedSession?->name ? 'Compared with the previous session' : 'Based on active records',
                'tone' => 'red',
                'href' => route('admin.students.index', $reportQuery),
                'actionLabel' => 'Open Report',
            ],
            [
                'key' => 'attendance',
                'title' => 'Average Attendance',
                'value' => number_format($attendanceSummary['rate'], 1),
                'suffix' => '%',
                'trend' => $this->formatTrend($attendanceSummary['rate'], $previousAttendanceSummary['rate'])['text'],
                'trendDirection' => $this->formatTrend($attendanceSummary['rate'], $previousAttendanceSummary['rate'])['direction'],
                'note' => number_format($attendanceSummary['present']) . ' present out of ' . number_format($attendanceSummary['total']) . ' records',
                'tone' => 'amber',
                'href' => '#attendance-analytics',
                'actionLabel' => 'View Details',
            ],
            [
                'key' => 'passRate',
                'title' => 'Overall Pass Rate',
                'value' => number_format($marksSummary['pass_rate'], 1),
                'suffix' => '%',
                'trend' => $this->formatTrend($marksSummary['pass_rate'], $previousMarksSummary['pass_rate'])['text'],
                'trendDirection' => $this->formatTrend($marksSummary['pass_rate'], $previousMarksSummary['pass_rate'])['direction'],
                'note' => number_format($marksSummary['passed']) . ' pass records across published marks',
                'tone' => 'green',
                'href' => route('admin.exams.index'),
                'actionLabel' => 'Open Exams',
            ],
            [
                'key' => 'averageMarks',
                'title' => 'Average Marks',
                'value' => number_format($marksSummary['average_score'], 1),
                'suffix' => '%',
                'trend' => $this->formatTrend($marksSummary['average_score'], $previousMarksSummary['average_score'])['text'],
                'trendDirection' => $this->formatTrend($marksSummary['average_score'], $previousMarksSummary['average_score'])['direction'],
                'note' => number_format($currentMarks->count()) . ' published mark records',
                'tone' => 'slate',
                'href' => route('admin.exams.index'),
                'actionLabel' => 'Open Report',
            ],
        ];

        $departmentAttendanceComparison = $departmentRows
            ->take(6)
            ->values()
            ->map(fn (array $row) => [
                'label' => $row['label'],
                'value' => $row['attendance_rate'] ?? 0,
                'note' => $row['has_data'] ? 'Attendance' : 'No data',
                'href' => route('admin.students.index', $this->buildStudentReportQuery($selectedSession, $selectedDepartment, $selectedProgram, $row['department_id'], null)),
            ])
            ->all();

        $departmentComparison = $departmentRows
            ->take(6)
            ->values()
            ->map(fn (array $row) => [
                'label' => $row['label'],
                'attendance' => $row['attendance_rate'] ?? 0,
                'pass' => $row['pass_rate'] ?? 0,
                'score' => $row['score'] ?? 0,
                'href' => route('admin.students.index', $this->buildStudentReportQuery($selectedSession, $selectedDepartment, $selectedProgram, $row['department_id'], null)),
            ])
            ->all();

        $topDepartments = $departmentRows->take(3)->values()->all();
        $topPrograms = $programRows->take(3)->values()->all();

        return [
            'summaryCards' => $summaryCards,
            'attendanceTrend' => $attendanceTrend,
            'departmentAttendanceComparison' => $departmentAttendanceComparison,
            'departmentComparison' => $departmentComparison,
            'topDepartments' => $topDepartments,
            'topPrograms' => $topPrograms,
            'alerts' => $alerts,
            'examStats' => [
                'total' => $totalExams,
                'completed' => $completedExams,
            ],
            'reportHref' => route('admin.students.index', $reportQuery),
            'selectedSessionLabel' => $selectedSession?->name ?? 'Current session',
            'selectedDepartmentLabel' => $selectedDepartment?->name,
            'selectedProgramLabel' => $selectedProgram?->name,
            'sessionOptions' => $filters['sessionOptions']->map(fn (AcademicSession $session) => [
                'id' => $session->id,
                'label' => trim($session->name . ($session->name_bs ? ' / ' . $session->name_bs : '')),
                'selected' => $selectedSession?->id === $session->id,
            ])->values()->all(),
            'departmentOptions' => $filters['departmentOptions']->map(fn (Department $department) => [
                'id' => $department->id,
                'label' => trim(($department->code ? $department->code . ' - ' : '') . $department->name),
                'selected' => $selectedDepartment?->id === $department->id,
                'count' => (int) ($department->students_count ?? 0),
            ])->values()->all(),
            'programOptions' => $filters['programOptions']->map(fn (Program $program) => [
                'id' => $program->id,
                'label' => trim(($program->code ? $program->code . ' - ' : '') . $program->name),
                'department' => $program->department?->name,
                'selected' => $selectedProgram?->id === $program->id,
            ])->values()->all(),
        ];
    }

    private function buildDetailState(array $filters, int $detailPage): ?array
    {
        $selectedSession = $filters['selectedSession'];
        $selectedDepartment = $filters['selectedDepartment'];
        $selectedProgram = $filters['selectedProgram'];

        $query = Student::active()
            ->with(['user', 'department', 'program'])
            ->when($selectedSession, fn ($builder) => $builder->where('academic_session_id', $selectedSession->id))
            ->when($selectedDepartment, fn ($builder) => $builder->where('department_id', $selectedDepartment->id))
            ->when($selectedProgram, fn ($builder) => $builder->where('program_id', $selectedProgram->id))
            ->orderBy('roll_number')
            ->orderBy('id');

        $students = $query->paginate(8, ['*'], 'detail_page', $detailPage);

        $studentIds = $students->getCollection()->pluck('id')->all();

        $attendanceRecords = Attendance::query()
            ->with(['attendanceSession'])
            ->whereIn('student_id', $studentIds)
            ->when($selectedSession, fn ($builder) => $builder->whereHas('attendanceSession', fn ($sessionQuery) => $sessionQuery->where('academic_session_id', $selectedSession->id)))
            ->get()
            ->groupBy('student_id');

        $marks = Mark::query()
            ->published()
            ->with(['exam', 'subject'])
            ->whereIn('student_id', $studentIds)
            ->when($selectedSession, fn ($builder) => $builder->whereHas('exam', fn ($examQuery) => $examQuery->where('academic_session_id', $selectedSession->id)))
            ->get()
            ->groupBy('student_id');

        $rows = $students->getCollection()->map(function (Student $student) use ($attendanceRecords, $marks) {
            $studentAttendance = $attendanceRecords->get($student->id, collect());
            $studentMarks = $marks->get($student->id, collect());
            $attendanceSummary = $this->summarizeAttendance($studentAttendance);
            $marksSummary = $this->summarizeMarks($studentMarks);

            $examMarks = $studentMarks
                ->groupBy(fn (Mark $mark) => $mark->exam_id)
                ->map(function (Collection $examMarks) {
                    $percentageValues = $examMarks
                        ->map(fn (Mark $mark) => $this->calculateMarkPercentage($mark))
                        ->filter(fn ($value) => $value !== null)
                        ->values();

                    $exam = $examMarks->first()?->exam;

                    return [
                        'exam' => $exam?->name ?? 'Exam',
                        'score' => $percentageValues->isNotEmpty() ? round($percentageValues->avg(), 1) : 0,
                        'status' => $examMarks->contains(fn (Mark $mark) => $mark->is_passed) ? 'Pass' : 'Review',
                    ];
                })
                ->sortByDesc('score')
                ->take(3)
                ->values()
                ->all();

            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'rollNumber' => $student->roll_number ?: 'N/A',
                'department' => $student->department?->name ?? 'N/A',
                'program' => $student->program?->name ?? 'N/A',
                'attendanceRate' => $attendanceSummary['rate'],
                'attendanceLabel' => number_format($attendanceSummary['present']) . ' / ' . number_format($attendanceSummary['total']) . ' records',
                'averageMarks' => $marksSummary['average_score'],
                'passRate' => $marksSummary['pass_rate'],
                'marksLabel' => number_format($marksSummary['average_score'], 1) . '% average',
                'examMarks' => $examMarks,
                'href' => route('admin.students.show', $student),
            ];
        })->values()->all();

        $scopeParts = array_filter([
            $selectedSession?->name,
            $selectedDepartment?->name,
            $selectedProgram?->name,
        ]);

        $reportQuery = $this->buildStudentReportQuery(
            $selectedSession,
            $selectedDepartment,
            $selectedProgram
        );

        $scopeAttendance = $this->summarizeAttendance($attendanceRecords->flatten(1));
        $scopeMarks = $this->summarizeMarks($marks->flatten(1));

        return [
            'scopeLabel' => $scopeParts ? implode(' · ', $scopeParts) : 'All students',
            'students' => $rows,
            'pagination' => [
                'currentPage' => $students->currentPage(),
                'lastPage' => $students->lastPage(),
                'perPage' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
            'summary' => [
                'students' => $students->total(),
                'attendanceRate' => $scopeAttendance['rate'],
                'averageMarks' => $scopeMarks['average_score'],
                'passRate' => $scopeMarks['pass_rate'],
            ],
            'reportHref' => route('admin.students.index', $reportQuery),
            'emptyMessage' => $students->total() > 0
                ? null
                : 'No student records matched the selected filters.',
        ];
    }

    private function summaryCacheKey(array $filters): string
    {
        return sprintf(
            'admin_analytics_summary:%s:%s:%s',
            $filters['selectedSession']?->id ?? 'none',
            $filters['selectedDepartment']?->id ?? 'none',
            $filters['selectedProgram']?->id ?? 'none'
        );
    }

    private function detailCacheKey(array $filters, int $detailPage): string
    {
        return sprintf(
            'admin_analytics_detail:%s:%s:%s:%s',
            $filters['selectedSession']?->id ?? 'none',
            $filters['selectedDepartment']?->id ?? 'none',
            $filters['selectedProgram']?->id ?? 'none',
            $detailPage
        );
    }

    private function resolveSession(?int $sessionId, Collection $sessions, ?AcademicSession $fallback): ?AcademicSession
    {
        if ($sessionId) {
            $matched = $sessions->firstWhere('id', $sessionId);

            if ($matched) {
                return $matched;
            }
        }

        return $fallback ?? $sessions->first();
    }

    private function resolvePreviousSession(?AcademicSession $currentSession): ?AcademicSession
    {
        if (!$currentSession?->start_date) {
            return AcademicSession::query()
                ->whereNotNull('end_date')
                ->orderByDesc('end_date')
                ->skip(1)
                ->first();
        }

        return AcademicSession::query()
            ->whereNotNull('end_date')
            ->where('end_date', '<', $currentSession->start_date)
            ->orderByDesc('end_date')
            ->first();
    }

    private function resolveWindow(?AcademicSession $session): array
    {
        $now = Carbon::now();

        if ($session?->start_date && $session?->end_date) {
            $start = Carbon::parse($session->start_date)->startOfDay();
            $end = Carbon::parse($session->end_date)->endOfDay();
            $days = max($start->diffInDays($end) + 1, 1);

            return [
                'start' => $start,
                'end' => $end,
                'label' => $session->name ?? 'Selected session',
                'bucketType' => $days > 120 ? 'month' : 'day',
            ];
        }

        return [
            'start' => $now->copy()->subDays(29)->startOfDay(),
            'end' => $now->copy()->endOfDay(),
            'label' => 'Last 30 days',
            'bucketType' => 'day',
        ];
    }

    private function loadAttendanceRecords(?AcademicSession $session, ?Department $department, ?Program $program): Collection
    {
        if (!$session) {
            return collect();
        }

        return Attendance::query()
            ->with(['attendanceSession', 'student.department', 'student.program'])
            ->whereHas('student', fn ($query) => $query->active())
            ->whereHas('attendanceSession', fn ($query) => $query->where('academic_session_id', $session->id))
            ->when($department, fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('department_id', $department->id)))
            ->when($program, fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('program_id', $program->id)))
            ->get();
    }

    private function loadMarks(?AcademicSession $session, ?Department $department, ?Program $program): Collection
    {
        if (!$session) {
            return collect();
        }

        return Mark::query()
            ->published()
            ->with(['exam', 'subject', 'student.department', 'student.program'])
            ->whereHas('student', fn ($query) => $query->active())
            ->whereHas('exam', fn ($query) => $query->where('academic_session_id', $session->id))
            ->when($department, fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('department_id', $department->id)))
            ->when($program, fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('program_id', $program->id)))
            ->get();
    }

    private function countStudents(?AcademicSession $session, ?Department $department, ?Program $program): int
    {
        return Student::active()
            ->when($session, fn ($query) => $query->where('academic_session_id', $session->id))
            ->when($department, fn ($query) => $query->where('department_id', $department->id))
            ->when($program, fn ($query) => $query->where('program_id', $program->id))
            ->count();
    }

    private function summarizeAttendance(Collection $attendanceRecords): array
    {
        $total = $attendanceRecords->count();
        $present = $attendanceRecords->where('status', 'present')->count();

        return [
            'total' => $total,
            'present' => $present,
            'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0.0,
        ];
    }

    private function summarizeMarks(Collection $marks): array
    {
        $percentages = $marks
            ->map(fn (Mark $mark) => $this->calculateMarkPercentage($mark))
            ->filter(fn ($value) => $value !== null)
            ->values();

        $total = $marks->count();
        $passed = $marks->filter(fn (Mark $mark) => $mark->is_passed)->count();

        return [
            'total' => $total,
            'passed' => $passed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0.0,
            'average_score' => $percentages->isNotEmpty() ? round($percentages->avg(), 1) : 0.0,
        ];
    }

    private function buildAttendanceTrend(Collection $attendanceRecords, array $window): array
    {
        $buckets = $this->makeBuckets($window['start'], $window['end'], $window['bucketType']);

        foreach ($attendanceRecords as $record) {
            $attendanceSessionDate = $record->attendanceSession?->date;

            if (!$attendanceSessionDate) {
                continue;
            }

            $date = Carbon::parse($attendanceSessionDate);
            $bucketKey = $window['bucketType'] === 'month' ? $date->format('Y-m') : $date->format('Y-m-d');

            if (!isset($buckets[$bucketKey])) {
                continue;
            }

            $buckets[$bucketKey]['total']++;
            if ($record->status === 'present') {
                $buckets[$bucketKey]['present']++;
            }
        }

        return [
            'labels' => array_values(array_map(static fn (array $bucket) => $bucket['label'], $buckets)),
            'values' => array_values(array_map(static fn (array $bucket) => $bucket['total'] > 0 ? round(($bucket['present'] / $bucket['total']) * 100, 1) : 0, $buckets)),
        ];
    }

    private function buildDepartmentRows(Collection $attendanceRecords, Collection $marks): Collection
    {
        $departments = Department::active()
            ->withCount('students')
            ->get();

        return $departments
            ->map(function (Department $department) use ($attendanceRecords, $marks) {
                $departmentAttendance = $attendanceRecords->filter(fn (Attendance $attendance) => (int) data_get($attendance, 'student.department_id') === (int) $department->id);
                $departmentMarks = $marks->filter(fn (Mark $mark) => (int) data_get($mark, 'student.department_id') === (int) $department->id);

                $attendanceSummary = $this->summarizeAttendance($departmentAttendance);
                $marksSummary = $this->summarizeMarks($departmentMarks);

                $score = match (true) {
                    $attendanceSummary['rate'] > 0 && $marksSummary['pass_rate'] > 0 => round(($attendanceSummary['rate'] * 0.45) + ($marksSummary['pass_rate'] * 0.55), 1),
                    $attendanceSummary['rate'] > 0 => $attendanceSummary['rate'],
                    $marksSummary['pass_rate'] > 0 => $marksSummary['pass_rate'],
                    default => 0.0,
                };

                return [
                    'department_id' => $department->id,
                    'name' => $department->name,
                    'code' => $department->code,
                    'label' => $department->code ?: Str::limit($department->name, 16),
                    'students' => (int) ($department->students_count ?? 0),
                    'attendance_rate' => $attendanceSummary['rate'],
                    'pass_rate' => $marksSummary['pass_rate'],
                    'average_marks' => $marksSummary['average_score'],
                    'score' => $score,
                    'has_data' => $departmentAttendance->isNotEmpty() || $departmentMarks->isNotEmpty(),
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    private function buildProgramRows(Collection $attendanceRecords, Collection $marks): Collection
    {
        $programs = Program::active()
            ->with('department')
            ->withCount('students')
            ->get();

        return $programs
            ->map(function (Program $program) use ($attendanceRecords, $marks) {
                $programAttendance = $attendanceRecords->filter(fn (Attendance $attendance) => (int) data_get($attendance, 'student.program_id') === (int) $program->id);
                $programMarks = $marks->filter(fn (Mark $mark) => (int) data_get($mark, 'student.program_id') === (int) $program->id);

                $attendanceSummary = $this->summarizeAttendance($programAttendance);
                $marksSummary = $this->summarizeMarks($programMarks);

                $score = match (true) {
                    $attendanceSummary['rate'] > 0 && $marksSummary['pass_rate'] > 0 => round(($attendanceSummary['rate'] * 0.45) + ($marksSummary['pass_rate'] * 0.55), 1),
                    $attendanceSummary['rate'] > 0 => $attendanceSummary['rate'],
                    $marksSummary['pass_rate'] > 0 => $marksSummary['pass_rate'],
                    default => 0.0,
                };

                return [
                    'program_id' => $program->id,
                    'department_id' => $program->department_id,
                    'name' => $program->name,
                    'code' => $program->code,
                    'label' => $program->code ?: Str::limit($program->name, 18),
                    'department' => $program->department?->name,
                    'students' => (int) ($program->students_count ?? 0),
                    'attendance_rate' => $attendanceSummary['rate'],
                    'pass_rate' => $marksSummary['pass_rate'],
                    'average_marks' => $marksSummary['average_score'],
                    'score' => $score,
                    'has_data' => $programAttendance->isNotEmpty() || $programMarks->isNotEmpty(),
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    private function buildAlerts(Collection $departmentRows): array
    {
        $alerts = [];

        $lowAttendance = $departmentRows
            ->filter(fn (array $row) => $row['attendance_rate'] !== null && $row['attendance_rate'] < 75)
            ->take(3)
            ->values();

        foreach ($lowAttendance as $row) {
            $alerts[] = [
                'tone' => 'warning',
                'title' => 'Low attendance in ' . $row['name'],
                'message' => 'Attendance is ' . number_format($row['attendance_rate'], 1) . '% for this department. Review class engagement.',
                'actionLabel' => 'Open Students',
                'actionHref' => route('admin.students.index', ['department_id' => $row['department_id']]),
            ];
        }

        $lowResults = $departmentRows->first(fn (array $row) => $row['pass_rate'] !== null && $row['pass_rate'] < 70);

        if ($lowResults) {
            $alerts[] = [
                'tone' => 'danger',
                'title' => 'Pass rate needs attention',
                'message' => $lowResults['name'] . ' is below the desired pass-rate target for published marks.',
                'actionLabel' => 'Open Exams',
                'actionHref' => route('admin.exams.index'),
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'tone' => 'success',
                'title' => 'Analytics look stable',
                'message' => 'No immediate academic issues were detected in the selected session.',
                'actionLabel' => 'Open Report',
                'actionHref' => route('admin.students.index'),
            ];
        }

        return array_slice($alerts, 0, 4);
    }

    private function buildStudentReportQuery(?AcademicSession $session, ?Department $department, ?Program $program, ?int $departmentOverride = null, ?int $programOverride = null): array
    {
        $query = [];

        if ($session?->id) {
            $query['academic_session_id'] = $session->id;
        }

        $departmentId = $departmentOverride ?? $department?->id;
        $programId = $programOverride ?? $program?->id;

        if ($departmentId) {
            $query['department_id'] = $departmentId;
        }

        if ($programId) {
            $query['program_id'] = $programId;
        }

        return $query;
    }

    private function calculateMarkPercentage(Mark $mark): ?float
    {
        $subject = $mark->subject;

        if (!$subject) {
            return null;
        }

        $fullMarks = ($subject->full_marks_internal_theory ?? 0)
            + ($subject->full_marks_external_theory ?? 0)
            + ($subject->full_marks_internal_practical ?? 0)
            + ($subject->full_marks_external_practical ?? 0);

        if ($fullMarks <= 0) {
            return null;
        }

        return round(($mark->total_marks / $fullMarks) * 100, 1);
    }

    private function formatTrend(float|int $current, float|int $previous): array
    {
        if ($previous <= 0) {
            return [
                'text' => $current > 0 ? '+100.0%' : '0.0%',
                'direction' => $current > 0 ? 'up' : 'flat',
            ];
        }

        $delta = (($current - $previous) / $previous) * 100;

        return [
            'text' => ($delta > 0 ? '+' : '') . number_format(abs($delta), 1) . '%',
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
        ];
    }

    private function makeBuckets(Carbon $start, Carbon $end, string $bucketType): array
    {
        $cursor = $bucketType === 'month'
            ? $start->copy()->startOfMonth()
            : $start->copy()->startOfDay();

        $buckets = [];

        while ($cursor <= $end) {
            $key = $bucketType === 'month' ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            $label = $bucketType === 'month' ? $cursor->format('M Y') : $cursor->format('d M');

            $buckets[$key] = [
                'label' => $label,
                'present' => 0,
                'total' => 0,
            ];

            $cursor = $bucketType === 'month'
                ? $cursor->copy()->addMonthNoOverflow()->startOfMonth()
                : $cursor->copy()->addDay();
        }

        return $buckets;
    }
}