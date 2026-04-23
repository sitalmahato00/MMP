<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Teacher, TimetableSlot, Notice, AttendanceSession, Assignment};
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

        $cacheKey = "teacher_dashboard_{$teacher->id}_v2";
        $data = Cache::remember($cacheKey, 300, function () use ($teacher) {
            $subjectsCount = $teacher->subjects()->count();
            
            // Sessions conducted this month
            $sessionsThisMonth = AttendanceSession::where('teacher_id', $teacher->id)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->count();
            
            // Pending assignments
            $pendingAssignments = Assignment::whereHas('subject.teachers', fn($q) => $q->where('teachers.id', $teacher->id))
                ->where('due_date', '>=', now())
                ->count();
            
            return [
                'subjects_count' => $subjectsCount,
                'sessions_this_month' => $sessionsThisMonth,
                'pending_assignments' => $pendingAssignments,
            ];
        });

        $todaySlots = collect();
        if ($teacher) {
            $today = strtolower(now()->format('l'));
            $todaySlots = Cache::remember("teacher_dashboard_slots:{$teacher->id}:{$today}", 300, function () use ($teacher, $today) {
                return TimetableSlot::with(['subject', 'timetable.program'])
                    ->where('teacher_id', $teacher->id)
                    ->where('day_of_week', $today)
                    ->orderBy('start_time')
                    ->get();
            });
        }

        $subjects = Cache::remember("teacher_dashboard_subjects:{$teacher->id}", 300, function () use ($teacher) {
            return $teacher->subjects()->with('program')->get();
        });

        $recentNotices = Cache::remember("teacher_dashboard_notices_{$teacher->department_id}", 300, function () use ($teacher) {
            return Notice::published()
                ->where(function($q) use ($teacher) {
                    $q->whereNull('department_id')
                      ->orWhere('department_id', $teacher->department_id);
                })
                ->with('author')
                ->latest()
                ->take(5)
                ->get();
        });

        // CTEVT notices (from official CTEVT website)
        $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(5);
        $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(5);

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('teacher.dashboard', compact('teacher', 'session', 'todaySlots', 'subjects', 'recentNotices', 'ctevtGeneralNotices', 'ctevtResultNotices', 'data', 'greeting', 'lastUpdated'));
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
