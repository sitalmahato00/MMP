<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice};
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $parent = $user->parentProfile;
        $parentId = $parent?->id ?? 'guest';

        $children = Cache::remember("parent_dashboard_children:{$parentId}", 300, function () use ($parent) {
            return $parent?->children()
                ->with(['user', 'department', 'program', 'attendances', 'marks', 'submissions'])
                ->get() ?? collect();
        });

        $session = AcademicSession::current();

        $recentNotices = Cache::remember('parent_dashboard_notices', 300, function () {
            return Notice::published()->latest()->take(5)->get();
        });

        // Compute per-child summaries
        $childrenSummaries = $children->map(function ($student) {
            $totalAtt = $student->attendances->count();
            $present = $student->attendances->where('status', 'present')->count();
            $attPct = $totalAtt > 0 ? round(($present / $totalAtt) * 100) : null;

            $publishedMarks = $student->marks->where('status', 'published');
            $avgMarks = $publishedMarks->count() > 0
                ? round($publishedMarks->avg(fn($m) => ($m->theory ?? 0) + ($m->practical ?? 0)), 1)
                : null;

            $pendingAssignments = $student->submissions
                ->where('status', 'pending')->count();

            return [
                'student' => $student,
                'attendancePct' => $attPct,
                'avgMarks' => $avgMarks,
                'totalExams' => $publishedMarks->count(),
                'pendingAssignments' => $pendingAssignments,
            ];
        });

        return view('parent.dashboard', compact('parent', 'children', 'childrenSummaries', 'session', 'recentNotices'));
    }
}
