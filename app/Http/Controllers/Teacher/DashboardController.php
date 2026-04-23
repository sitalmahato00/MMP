<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Teacher, TimetableSlot, Notice, AttendanceSession, Attendance};
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected PublicDataService $publicDataService;

    public function __construct(PublicDataService $publicDataService)
    {
        $this->publicDataService = $publicDataService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get today's classes
        $today = strtolower(now()->format('l')); // e.g., 'monday', 'tuesday', etc.
        $todayClasses = TimetableSlot::with(['subject', 'timetable.program', 'timetable'])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->whereHas('timetable', function($q) {
                $q->where('is_active', true);
            })
            ->orderBy('start_time')
            ->get();

        // Debug: Log today's day and class count
        \Log::info('Teacher Dashboard - Today Classes', [
            'teacher_id' => $teacher->id,
            'today' => $today,
            'today_full' => now()->format('l'),
            'classes_count' => $todayClasses->count(),
        ]);

        // Get all my classes (subjects)
        $myClasses = $teacher->subjects()->with('program')->get();

        // Get recent notices
        $notices = Notice::published()
            ->where(function($q) use ($teacher) {
                $q->whereNull('department_id')
                  ->orWhere('department_id', $teacher->department_id);
            })
            ->with('author')
            ->latest()
            ->take(5)
            ->get();

        // CTEVT notices (from official CTEVT website)
        $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(5);
        $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(5);

        // Get attendance data for chart (last 30 days)
        $attendanceData = $this->getAttendanceChartData($teacher->id);

        $greeting = $this->getGreeting();

        return view('teacher.dashboard', compact(
            'teacher',
            'session',
            'todayClasses',
            'myClasses',
            'notices',
            'ctevtGeneralNotices',
            'ctevtResultNotices',
            'attendanceData',
            'greeting'
        ));
    }

    private function getAttendanceChartData($teacherId)
    {
        // Get last 7 days from today
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $sessions = AttendanceSession::where('teacher_id', $teacherId)
                ->whereDate('date', $date->toDateString())
                ->get();

            $totalPresent = 0;
            $totalStudents = 0;

            foreach ($sessions as $session) {
                $total = Attendance::where('attendance_session_id', $session->id)->count();
                $present = Attendance::where('attendance_session_id', $session->id)
                    ->where('status', 'present')
                    ->count();
                
                $totalStudents += $total;
                $totalPresent += $present;
            }
            
            $rate = $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100, 1) : 0;
            
            $chartData[] = [
                'date' => $date->format('M d'),
                'date_bs' => bsDate($date, 'Y F d, l'), // Full BS format
                'date_short' => bsDate($date, 'F d'), // Short format for chart
                'rate' => $rate,
            ];
        }

        return $chartData;
    }

    private function getGreeting(): string
    {
        $hour = Carbon::now()->hour;
        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening'
        };
    }
}
