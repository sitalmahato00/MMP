<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Teacher, TimetableSlot, Notice, AttendanceSession, Attendance, Program};
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $today = strtolower(now()->format('l'));
        $todayClasses = TimetableSlot::with(['subject', 'timetable.program', 'timetable'])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $today)
            ->whereHas('timetable', function($q) {
                $q->where('is_active', true);
            })
            ->orderBy('start_time')
            ->get();

        // Get all my classes (subjects)
        $myClasses = Cache::remember("teacher_subjects_{$teacher->id}", 300, function () use ($teacher) {
            return $teacher->subjects()->with('program')->get();
        });

        $programIds = Cache::remember("teacher_program_ids_{$teacher->department_id}", 600, function () use ($teacher) {
            return Program::where('department_id', $teacher->department_id)->pluck('id')->all();
        });

        // Get recent notices
        $notices = Cache::remember("teacher_notices_{$teacher->department_id}_v1", 300, function () use ($teacher, $programIds) {
            return Notice::published()
                ->visibleToDepartmentContext($teacher->department_id, $programIds)
                ->forNoticeBoard()
                ->with('author')
                ->latest()
                ->take(5)
                ->get();
        });

        // Get attendance chart data (last 7 days, single grouped query)
        $attendanceData = $this->getAttendanceChartData($teacher->id);

        $greeting = $this->getGreeting();

        return view('teacher.dashboard', compact(
            'teacher',
            'session',
            'todayClasses',
            'myClasses',
            'notices',
            'attendanceData',
            'greeting'
        ));
    }

    private function getAttendanceChartData(int $teacherId): array
    {
        return Cache::remember("teacher_attendance_chart_{$teacherId}_v1", 300, function () use ($teacherId) {
            $sevenDaysAgo = Carbon::now()->subDays(6)->toDateString();

            // 1 grouped query: join sessions → attendances, filter by teacher + date range
            $rows = AttendanceSession::where('teacher_id', $teacherId)
                ->where('date', '>=', $sevenDaysAgo)
                ->join('attendances', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
                ->selectRaw("attendance_sessions.date as att_date,
                             COUNT(*) as total,
                             SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->groupBy('attendance_sessions.date')
                ->get()
                ->keyBy('att_date');

            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $row  = $rows->get($date->toDateString());
                $total   = (int) ($row->total   ?? 0);
                $present = (int) ($row->present ?? 0);

                $chartData[] = [
                    'date'       => $date->toDateString(),
                    'date_bs'    => bsDate($date, 'Y F d, l'),
                    'date_short' => bsDate($date, 'F d'),
                    'rate'       => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                ];
            }

            return $chartData;
        });
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
