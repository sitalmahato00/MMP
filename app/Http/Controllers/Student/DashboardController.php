<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice, Assignment, Attendance, Mark, TimetableSlot, AttendanceSession};
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

        // Get attendance data for chart (last 7 days)
        $attendanceChartData = $this->getAttendanceChartData($student);
        
        // Get grade distribution for pie chart
        $gradeDistribution = $this->getGradeDistribution($student);
        
        // Get KPI data
        $kpiData = $this->getKpiData($student);

        // Get notices with CTEVT categorization
        $notices = $this->getNoticesData($student);

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

        return view('student.dashboard', compact(
            'student', 
            'session', 
            'notices', 
            'todaySlots', 
            'kpiData', 
            'attendanceChartData',
            'gradeDistribution',
            'greeting', 
            'lastUpdated'
        ));
    }

    private function getAttendanceChartData($student)
    {
        $days = [];
        $attendanceData = [];
        
        // Get last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('Y-m-d');
            
            // Get attendance for this day
            $attendance = Attendance::where('student_id', $student->id)
                ->whereHas('attendanceSession', function($q) use ($date) {
                    $q->where('date', $date->format('Y-m-d'));
                })
                ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                ->selectRaw("COUNT(*) as total")
                ->first();
            
            $percentage = $attendance && $attendance->total > 0 
                ? round(($attendance->present / $attendance->total) * 100, 1) 
                : 0;
                
            $attendanceData[] = $percentage;
        }
        
        return [
            'labels' => $days,
            'data' => $attendanceData
        ];
    }

    private function getGradeDistribution($student)
    {
        $grades = Mark::where('student_id', $student->id)
            ->where('status', 'published')
            ->selectRaw('
                SUM(CASE WHEN total_marks >= 80 THEN 1 ELSE 0 END) as distinction,
                SUM(CASE WHEN total_marks >= 60 AND total_marks < 80 THEN 1 ELSE 0 END) as first_division,
                SUM(CASE WHEN total_marks >= 45 AND total_marks < 60 THEN 1 ELSE 0 END) as second_division,
                SUM(CASE WHEN total_marks >= 32 AND total_marks < 45 THEN 1 ELSE 0 END) as third_division,
                SUM(CASE WHEN total_marks < 32 THEN 1 ELSE 0 END) as fail
            ')
            ->first();

        return [
            'labels' => ['Distinction (80%+)', 'First Division (60-79%)', 'Second Division (45-59%)', 'Third Division (32-44%)', 'Fail (<32%)'],
            'data' => [
                $grades->distinction ?? 0,
                $grades->first_division ?? 0,
                $grades->second_division ?? 0,
                $grades->third_division ?? 0,
                $grades->fail ?? 0
            ],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#6b7280']
        ];
    }

    private function getKpiData($student)
    {
        $cacheKey = "student_dashboard_kpi_{$student->id}_v3";
        return Cache::remember($cacheKey, 300, function () use ($student) {
            // Overall attendance rate
            $attendanceData = Attendance::where('student_id', $student->id)
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

            // Average grade
            $avgGrade = Mark::where('student_id', $student->id)
                ->where('status', 'published')
                ->avg('total_marks');

            // Total subjects
            $totalSubjects = Mark::where('student_id', $student->id)
                ->where('status', 'published')
                ->distinct('subject_id')
                ->count();

            return [
                'attendance_rate' => $attendanceRate,
                'pending_assignments' => $pendingAssignments,
                'average_grade' => $avgGrade ? round($avgGrade, 1) : 0,
                'total_subjects' => $totalSubjects,
            ];
        });
    }

    private function getNoticesData($student)
    {
        $cacheKey = "student_dashboard_notices_{$student->department_id}_v2";
        return Cache::remember($cacheKey, 300, function () use ($student) {
            $allNotices = Notice::where('is_published', true)
                ->where(function($q) use ($student) {
                    $q->whereNull('department_id')
                      ->orWhere('department_id', $student->department_id);
                })
                ->with('author')
                ->latest()
                ->take(20)
                ->get();

            return [
                'general' => $allNotices->where('type', 'general')->take(5),
                'ctevt' => $allNotices->where('type', 'exam')->take(5),
            ];
        });
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
