<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $parent = Auth::user()->parentProfile;
        $children = $parent?->children()
            ->with(['user', 'department', 'program', 'attendances' => fn($q) => $q->latest()])
            ->get() ?? collect();

        $childrenData = $children->map(function ($student) {
            $total = $student->attendances->count();
            $present = $student->attendances->where('status', 'present')->count();
            $absent = $student->attendances->where('status', 'absent')->count();
            $late = $student->attendances->where('status', 'late')->count();
            $pct = $total > 0 ? round(($present / $total) * 100) : null;

            return [
                'student' => $student,
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'pct' => $pct,
                'recentRecords' => $student->attendances->take(15),
            ];
        });

        return view('parent.attendance', compact('childrenData'));
    }
}
