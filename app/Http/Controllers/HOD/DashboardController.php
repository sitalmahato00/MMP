<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\{Student, Teacher, Department, AcademicSession, Notice, Attendance, AttendanceSession, Mark, Program};
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

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
        
        // Find department where this user is HOD
        $department = Department::where('hod_id', $user->id)->first();
        
        // Get department ID (null if not assigned)
        $deptId = $department?->id;
        $session = AcademicSession::current();

        // If no department assigned, show dashboard with empty/zero data
        if (!$deptId) {
            $data = [
                'student_count' => 0,
                'teacher_count' => 0,
                'program_count' => 0,
                'attendance_rate' => 0,
                'total_marks' => 0,
            ];
            
            $recentNotices = Notice::published()
                ->whereNull('department_id')
                ->forNoticeBoard()
                ->with(['author'])
                ->latest()
                ->take(5)
                ->get();

            // CTEVT notices (from official CTEVT website)
            $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(5);
            $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(5);
            
            $greeting = $this->greeting();
            $lastUpdated = now();
            
            return view('hod.dashboard', compact(
                'data',
                'department',
                'session',
                'recentNotices',
                'ctevtGeneralNotices',
                'ctevtResultNotices',
                'greeting',
                'lastUpdated'
            ));
        }

        $cacheKey = "hod_dashboard_{$deptId}_v2";
        $data = Cache::remember($cacheKey, 300, function () use ($deptId, $session) {
            $studentCount = Student::active()->where('department_id', $deptId)->count();
            $teacherCount = Teacher::active()->where('department_id', $deptId)->count();
            $programCount = Program::where('department_id', $deptId)->count();
            
            // Attendance rate for department (last 7 days)
            $sevenDaysAgo = Carbon::now()->subDays(7);
            $attendanceData = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->where('students.department_id', $deptId)
                ->where('attendance_sessions.date', '>=', $sevenDaysAgo->toDateString())
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            $attendanceRate = $attendanceData && $attendanceData->total > 0 
                ? round(($attendanceData->present / $attendanceData->total) * 100, 1) 
                : 0;

            // Pass rate (if marks exist)
            $marksData = Mark::query()
                ->join('students', 'students.id', '=', 'marks.student_id')
                ->where('students.department_id', $deptId)
                ->where('marks.status', 'published')
                ->selectRaw('COUNT(*) as total')
                ->first();

            $totalMarks = $marksData->total ?? 0;

            return [
                'student_count' => $studentCount,
                'teacher_count' => $teacherCount,
                'program_count' => $programCount,
                'attendance_rate' => $attendanceRate,
                'total_marks' => $totalMarks,
            ];
        });

        // Chart data for analytics (disable cache for debugging)
        $chartData = $this->getChartData($deptId);
        
        $recentNotices = Cache::remember("hod_dashboard_notices:{$deptId}_v2", 300, function () use ($deptId) {
            $programIds = Program::where('department_id', $deptId)->pluck('id')->all();

            return Notice::published()
                ->visibleToDepartmentContext($deptId, $programIds)
                ->forNoticeBoard()
                ->with(['author'])
                ->latest()
                ->take(5)
                ->get();
        });

        // CTEVT notices (from official CTEVT website)
        $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(5);
        $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(5);

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('hod.dashboard', compact(
            'data',
            'chartData',
            'department',
            'session',
            'recentNotices',
            'ctevtGeneralNotices',
            'ctevtResultNotices',
            'greeting',
            'lastUpdated'
        ));
    }

    private function getChartData($deptId)
    {
        // Grade distribution for active students (donut chart)
        $gradeDistribution = [];
        
        // Get all marks for active students in this department
        $marks = Mark::query()
            ->join('students', 'students.id', '=', 'marks.student_id')
            ->join('exams', 'exams.id', '=', 'marks.exam_id')
            ->where('students.department_id', $deptId)
            ->where('students.is_active', true)
            ->where('marks.status', 'published')
            ->with(['exam'])
            ->get();

        // Debug: Log the marks count
        \Log::info('Marks Query Results Count:', ['count' => $marks->count()]);

        // Calculate grades using the model's total_marks attribute
        foreach ($marks as $mark) {
            $totalMarks = $mark->total_marks; // This uses the getTotalMarksAttribute method
            
            $grade = match (true) {
                $totalMarks >= 90 => 'A+',
                $totalMarks >= 80 => 'A',
                $totalMarks >= 70 => 'B+',
                $totalMarks >= 60 => 'B',
                $totalMarks >= 50 => 'C',
                default => 'F'
            };
            
            $gradeDistribution[$grade] = ($gradeDistribution[$grade] ?? 0) + 1;
        }

        // Debug: Log the grade distribution results
        \Log::info('Grade Distribution Results:', $gradeDistribution);

        // If no real data, leave gradeDistribution empty (no sample data)

        // Fill missing grades with 0
        $allGrades = ['A+', 'A', 'B+', 'B', 'C', 'F'];
        foreach ($allGrades as $grade) {
            if (!isset($gradeDistribution[$grade])) {
                $gradeDistribution[$grade] = 0;
            }
        }

        // Attendance trend data (last 7 days with BS dates)
        $attendanceData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $attendanceStats = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->where('students.department_id', $deptId)
                ->where('attendance_sessions.date', $date->toDateString())
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            if ($attendanceStats && $attendanceStats->total > 0) {
                $attendanceRate = round(($attendanceStats->present / $attendanceStats->total) * 100, 1);
            } else {
                $attendanceRate = null; // No data for this day
            }

            $attendanceData[] = [
                'date' => $date->toDateString(),
                'date_bs' => bsDate($date, 'Y F d, l'),
                'date_short' => bsDate($date, 'F d'),
                'rate' => $attendanceRate
            ];
        }

        // Remove days with no data (rate === null)
        $attendanceData = array_filter($attendanceData, fn($d) => $d['rate'] !== null);
        
        // Today's classes for the department with attendance information
        $today = strtolower(Carbon::now()->format('l')); // Day name (monday, tuesday, etc.)
        $todayDate = Carbon::now()->toDateString();
        
        $todayClasses = \App\Models\TimetableSlot::query()
            ->join('timetables', 'timetables.id', '=', 'timetable_slots.timetable_id')
            ->join('subjects', 'subjects.id', '=', 'timetable_slots.subject_id')
            ->join('teachers', 'teachers.id', '=', 'timetable_slots.teacher_id')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->join('programs', 'programs.id', '=', 'timetables.program_id')
            ->where('programs.department_id', $deptId)
            ->where('timetable_slots.day_of_week', $today)
            ->where('timetables.is_active', true)
            ->select([
                'timetable_slots.id as slot_id',
                'timetable_slots.start_time',
                'timetable_slots.end_time',
                'timetable_slots.room_number',
                'timetable_slots.type',
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'users.name as teacher_name',
                'timetables.id as timetable_id',
                'timetables.semester',
                'timetables.section',
                'timetables.program_id',
                'programs.name as program_name',
                'programs.code as program_code'
            ])
            ->orderBy('timetable_slots.start_time')
            ->get()
            ->map(function($class) use ($todayDate) {
                $startTime = \Carbon\Carbon::parse($class->start_time)->format('g:i A');
                $endTime = \Carbon\Carbon::parse($class->end_time)->format('g:i A');
                
                // Check if attendance has been marked for this class today
                $attendanceSession = \App\Models\AttendanceSession::where('date', $todayDate)
                    ->where('program_id', $class->program_id)
                    ->where('semester', $class->semester)
                    ->where('subject_id', $class->subject_id)
                    ->where('section', $class->section)
                    ->first();
                
                $attendanceMarked = false;
                $totalStudentsMarked = 0;
                $presentCount = 0;
                $absentCount = 0;
                
                if ($attendanceSession) {
                    $attendanceStats = \App\Models\Attendance::where('attendance_session_id', $attendanceSession->id)
                        ->selectRaw('COUNT(*) as total')
                        ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                        ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent")
                        ->first();
                    
                    if ($attendanceStats && $attendanceStats->total > 0) {
                        $attendanceMarked = true;
                        $totalStudentsMarked = $attendanceStats->total;
                        $presentCount = $attendanceStats->present ?? 0;
                        $absentCount = $attendanceStats->absent ?? 0;
                    }
                }
                
                return [
                    'time' => $startTime . ' - ' . $endTime,
                    'subject' => $class->subject_name,
                    'subject_code' => $class->subject_code,
                    'teacher' => $class->teacher_name,
                    'room' => $class->room_number,
                    'type' => ucfirst($class->type),
                    'program' => $class->program_code . ' - Sem ' . $class->semester . ($class->section ? ' (' . $class->section . ')' : ''),
                    'program_full' => $class->program_name . ' (Semester ' . $class->semester . ($class->section ? ', Section ' . $class->section : '') . ')',
                    'attendance_marked' => $attendanceMarked,
                    'total_students_marked' => $totalStudentsMarked,
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                    'attendance_rate' => $totalStudentsMarked > 0 ? round(($presentCount / $totalStudentsMarked) * 100, 1) : 0
                ];
            });

        // Debug: Log today's classes query
        \Log::info('Today\'s Classes Query - Day: ' . $today . ', Department ID: ' . $deptId);
        \Log::info('Today\'s Classes Count: ' . $todayClasses->count());

        // If no classes today, leave todayClasses empty (no sample data)
        
        return [
            'grades' => $gradeDistribution,
            'attendance' => $attendanceData,
            'todayClasses' => $todayClasses
        ];
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
