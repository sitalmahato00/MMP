<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice};
use App\Services\PublicDataService;
use App\Services\StudentRecordService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected PublicDataService $publicDataService;
    protected StudentRecordService $studentRecordService;

    public function __construct(PublicDataService $publicDataService, StudentRecordService $studentRecordService)
    {
        $this->publicDataService = $publicDataService;
        $this->studentRecordService = $studentRecordService;
    }

    public function index()
    {
        $user = auth()->user();
        $parent = $user->parentProfile;

        if (!$parent) {
            abort(403, 'Parent profile not found');
        }

        $parentId = $parent->id;

        $children = Cache::remember("parent_dashboard_children:{$parentId}_v2", 300, function () use ($parent) {
            return $parent->children()
                ->with(['user', 'department', 'program'])
                ->get();
        });

        $session = AcademicSession::current();

        $recentNotices = Cache::remember('parent_dashboard_notices', 300, function () {
            return Notice::published()
                ->with(['author', 'department:id,name,code'])
                ->latest()
                ->take(5)
                ->get();
        });

        $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(5);
        $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(5);

        $childrenSummaries = $children->map(function ($student) use ($session) {
            $today = Carbon::today()->toDateString();
            $attendanceSummary = $this->studentRecordService->getAttendanceSummary($student);
            $publishedMarks = $this->studentRecordService->getVisiblePublishedMarks($student);
            $marksSummary = $this->studentRecordService->summarizeMarks($publishedMarks);

            $subjectAttendance = $student->attendances()
                ->with(['attendanceSession.subject:id,name,code,type'])
                ->whereHas('attendanceSession', function ($query) use ($session) {
                    if ($session) {
                        $query->where('academic_session_id', $session->id);
                    }
                })
                ->get()
                ->groupBy(function ($attendance) {
                    return $attendance->attendanceSession->subject_id;
                })
                ->map(function ($subjectAttendances) {
                    $subject = $subjectAttendances->first()->attendanceSession->subject;

                    [$classAttendances, $labAttendances] = $this->splitAttendanceBuckets($subjectAttendances, $subject->type);

                    $classTotal = $classAttendances->count();
                    $classPresent = $classAttendances->where('status', 'present')->count();
                    $labTotal = $labAttendances->count();
                    $labPresent = $labAttendances->where('status', 'present')->count();

                    return [
                        'subject_name' => $subject->name,
                        'subject_code' => $subject->code,
                        'subject_type' => $subject->type,
                        'class_percentage' => $classTotal > 0 ? round(($classPresent / $classTotal) * 100) : 0,
                        'class_total' => $classTotal,
                        'class_present' => $classPresent,
                        'lab_percentage' => $labTotal > 0 ? round(($labPresent / $labTotal) * 100) : 0,
                        'lab_total' => $labTotal,
                        'lab_present' => $labPresent,
                        'has_lab' => in_array($subject->type, ['practical', 'both']),
                    ];
                })
                ->values();

            $todayAttendance = $student->attendances()
                ->with(['attendanceSession.subject:id,name,code'])
                ->whereHas('attendanceSession', fn ($query) => $query->whereDate('date', $today))
                ->get()
                ->map(function ($attendance) {
                    $session = $attendance->attendanceSession;
                    $isLab = $this->isLabSession($session->period ?? null, $session->subject->type ?? null);

                    return [
                        'subject_name' => $session->subject->name,
                        'subject_code' => $session->subject->code,
                        'period' => $session->period,
                        'type' => $isLab ? 'Lab' : 'Class',
                        'status' => $attendance->status,
                        'time' => bsDateTime($attendance->created_at, '', 'h:i A'),
                    ];
                });

            $pendingAssignments = $student->submissions()
                ->where('status', 'pending')
                ->count();

            return [
                'student' => $student,
                'attendancePct' => $attendanceSummary['total'] > 0 ? $attendanceSummary['rate'] : null,
                'subjectAttendance' => $subjectAttendance,
                'todayAttendance' => $todayAttendance,
                'percentageRate' => $marksSummary['percentage_rate'],
                'publishedAssessments' => $marksSummary['total_assessments'],
                'distinctionAssessments' => $marksSummary['distinction_assessments'],
                'pendingAssignments' => $pendingAssignments,
            ];
        });

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('parent.dashboard', compact(
            'parent',
            'children',
            'childrenSummaries',
            'session',
            'recentNotices',
            'ctevtGeneralNotices',
            'ctevtResultNotices',
            'greeting',
            'lastUpdated'
        ));
    }

    private function greeting(): string
    {
        $hour = Carbon::now()->hour;

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    private function splitAttendanceBuckets($subjectAttendances, ?string $subjectType): array
    {
        $labAttendances = $subjectAttendances->filter(function ($attendance) use ($subjectType) {
            return $this->isLabSession($attendance->attendanceSession->period ?? null, $subjectType);
        })->values();

        $classAttendances = $subjectAttendances->reject(function ($attendance) use ($subjectType) {
            return $this->isLabSession($attendance->attendanceSession->period ?? null, $subjectType);
        })->values();

        return [$classAttendances, $labAttendances];
    }

    private function isLabSession(?string $period, ?string $subjectType): bool
    {
        $normalizedPeriod = strtolower(trim((string) $period));
        $normalizedType = strtolower(trim((string) $subjectType));

        if (str_contains($normalizedPeriod, 'lab')) {
            return true;
        }

        if (str_contains($normalizedPeriod, 'class')) {
            return false;
        }

        return $normalizedType === 'practical';
    }
}
