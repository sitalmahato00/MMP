<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice};
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
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
                ->with('author')
                ->latest()
                ->take(5)
                ->get();
        });

        // Compute per-child summaries
        $childrenSummaries = $children->map(function ($student) {
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            
            $attendances = $student->attendances()
                ->whereHas('attendanceSession', fn($q) => $q->where('date', '>=', $thirtyDaysAgo->toDateString()))
                ->get();
            
            $totalAtt = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $attPct = $totalAtt > 0 ? round(($present / $totalAtt) * 100) : null;

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
                'avgMarks' => $avgMarks,
                'totalExams' => $publishedMarks->count(),
                'pendingAssignments' => $pendingAssignments,
            ];
        });

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('parent.dashboard', compact('parent', 'children', 'childrenSummaries', 'session', 'recentNotices', 'greeting', 'lastUpdated'));
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
