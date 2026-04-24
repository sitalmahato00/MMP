<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice};
use App\Services\PublicDataService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected PublicDataService $publicDataService;

    public function __construct(PublicDataService $publicDataService)
    {
        $this->publicDataService = $publicDataService;
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

        // Get CTEVT notices (from official CTEVT website)
        $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(5);
        $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(5);

        // Compute per-child summaries
        $childrenSummaries = $children->map(function ($student) use ($session) {
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $today = Carbon::today()->toDateString();
            
            // Overall attendance (last 30 days)
            $attendances = $student->attendances()
                ->whereHas('attendanceSession', fn($q) => $q->where('date', '>=', $thirtyDaysAgo->toDateString()))
                ->get();
            
            $totalAtt = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $attPct = $totalAtt > 0 ? round(($present / $totalAtt) * 100) : null;

            // Subject-wise attendance with class/lab breakdown
            $subjectAttendance = $student->attendances()
                ->with(['attendanceSession.subject:id,name,code,type'])
                ->whereHas('attendanceSession', function($q) use ($session) {
                    if ($session) {
                        $q->where('academic_session_id', $session->id);
                    }
                })
                ->get()
                ->groupBy(function($attendance) {
                    return $attendance->attendanceSession->subject_id;
                })
                ->map(function($subjectAttendances) {
                    $subject = $subjectAttendances->first()->attendanceSession->subject;
                    
                    // Separate class and lab attendance
                    $classAttendances = $subjectAttendances->filter(function($att) {
                        return str_contains(strtolower($att->attendanceSession->period ?? ''), 'class');
                    });
                    
                    $labAttendances = $subjectAttendances->filter(function($att) {
                        return str_contains(strtolower($att->attendanceSession->period ?? ''), 'lab');
                    });
                    
                    $classTotal = $classAttendances->count();
                    $classPresent = $classAttendances->where('status', 'present')->count();
                    $classPct = $classTotal > 0 ? round(($classPresent / $classTotal) * 100) : 0;
                    
                    $labTotal = $labAttendances->count();
                    $labPresent = $labAttendances->where('status', 'present')->count();
                    $labPct = $labTotal > 0 ? round(($labPresent / $labTotal) * 100) : 0;
                    
                    return [
                        'subject_name' => $subject->name,
                        'subject_code' => $subject->code,
                        'subject_type' => $subject->type,
                        'class_percentage' => $classPct,
                        'class_total' => $classTotal,
                        'class_present' => $classPresent,
                        'lab_percentage' => $labPct,
                        'lab_total' => $labTotal,
                        'lab_present' => $labPresent,
                        'has_lab' => in_array($subject->type, ['practical', 'both']),
                    ];
                })
                ->values();

            // Today's marked attendance
            $todayAttendance = $student->attendances()
                ->with(['attendanceSession.subject:id,name,code'])
                ->whereHas('attendanceSession', fn($q) => $q->whereDate('date', $today))
                ->get()
                ->map(function($attendance) {
                    $session = $attendance->attendanceSession;
                    $isLab = str_contains(strtolower($session->period ?? ''), 'lab');
                    
                    return [
                        'subject_name' => $session->subject->name,
                        'subject_code' => $session->subject->code,
                        'period' => $session->period,
                        'type' => $isLab ? 'Lab' : 'Class',
                        'status' => $attendance->status,
                        'time' => bsDateTime($attendance->created_at, '', 'h:i A'),
                    ];
                });

            $publishedMarks = $student->marks()->where('status', 'published')->get();
            $avgMarks = $publishedMarks->count() > 0
                ? round($publishedMarks->avg(function($m) {
                    return ($m->internal_theory_marks ?? 0) + ($m->external_theory_marks ?? 0) 
                         + ($m->internal_practical_marks ?? 0) + ($m->external_practical_marks ?? 0);
                }), 1)
                : null;

            $pendingAssignments = $student->submissions()
                ->where('status', 'pending')
                ->count();

            return [
                'student' => $student,
                'attendancePct' => $attPct,
                'subjectAttendance' => $subjectAttendance,
                'todayAttendance' => $todayAttendance,
                'avgMarks' => $avgMarks,
                'totalExams' => $publishedMarks->count(),
                'pendingAssignments' => $pendingAssignments,
            ];
        });

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('parent.dashboard', compact('parent', 'children', 'childrenSummaries', 'session', 'recentNotices', 'ctevtGeneralNotices', 'ctevtResultNotices', 'greeting', 'lastUpdated'));
    }

    private function greeting(): string
    {
        $hour = Carbon::now()->hour;
        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening'
        };
    }
}
