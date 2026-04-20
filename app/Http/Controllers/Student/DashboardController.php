<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice, Assignment, Attendance, Mark, TimetableSlot};
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = $user->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $session = AcademicSession::current();
        $departmentId = $student->department_id ?? 'none';
        $programId = $student->program_id ?? 'none';
        $semester = $student->current_semester ?? 'none';

        $cacheKey = "student_dashboard_{$student->id}_v2";
        $data = Cache::remember($cacheKey, 300, function () use ($student) {
            // Attendance rate (last 30 days)
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $attendanceData = Attendance::where('student_id', $student->id)
                ->whereHas('attendanceSession', fn($q) => $q->where('date', '>=', $thirtyDaysAgo->toDateString()))
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            $attendanceRate = $attendanceData && $attendanceData->total > 0 
                ? round(($attendanceData->present / $attendanceData->total) * 100, 1) 
                : 0;

            // Pending assignments
            $pendingAssignments = Assignment::where('program_id', $student->program_id)
                ->where('semester', $student->current_semester)
                ->where('due_date', '>=', now())
                ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $student->id))
                ->count();

            // Published results
            $publishedResults = Mark::where('student_id', $student->id)
                ->where('status', 'published')
                ->count();

            return [
                'attendance_rate' => $attendanceRate,
                'pending_assignments' => $pendingAssignments,
                'published_results' => $publishedResults,
            ];
        });

        $recentNotices = Cache::remember("student_dashboard_notices:{$departmentId}", 300, function () use ($student) {
            return Notice::published()
                ->where(function($q) use ($student) {
                    $q->whereNull('department_id')
                      ->orWhere('department_id', $student->department_id);
                })
                ->with('author')
                ->latest()
                ->take(5)
                ->get();
        });

        $upcomingAssignments = Cache::remember("student_dashboard_assignments:{$programId}:{$semester}", 300, function () use ($student) {
            return Assignment::where('program_id', $student->program_id)
                ->where('semester', $student->current_semester)
                ->where('due_date', '>=', now())
                ->with('subject')
                ->orderBy('due_date')
                ->take(5)
                ->get();
        });

        // Today's classes
        $today = strtolower(now()->format('l'));
        $todaySlots = Cache::remember("student_dashboard_slots:{$programId}:{$semester}:{$today}", 300, function () use ($student, $today) {
            return TimetableSlot::whereHas('timetable', function($q) use ($student) {
                    $q->where('program_id', $student->program_id)
                      ->where('semester', $student->current_semester);
                })
                ->where('day_of_week', $today)
                ->with(['subject', 'teacher.user'])
                ->orderBy('start_time')
                ->get();
        });

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('student.dashboard', compact('student', 'session', 'recentNotices', 'upcomingAssignments', 'todaySlots', 'data', 'greeting', 'lastUpdated'));
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
