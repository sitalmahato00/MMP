<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class ChildController extends Controller
{
    public function show(Student $student)
    {
        $parent = Auth::user()->parentProfile;

        // Ensure this child belongs to the authenticated parent
        abort_unless(
            $parent && $parent->students()->where('students.id', $student->id)->exists(),
            403,
            'This student is not linked to your account.'
        );

        $student->load([
            'user', 'department', 'program',
            'attendances' => fn($q) => $q->latest()->take(30),
            'marks.subject', 'marks.exam',
            'subjects',
            'timetableSlots.timetable',
        ]);

        // Attendance summary
        $totalAtt = $student->attendances->count();
        $present = $student->attendances->where('status', 'present')->count();
        $absent = $student->attendances->where('status', 'absent')->count();
        $late = $student->attendances->where('status', 'late')->count();
        $attendancePct = $totalAtt > 0 ? round(($present / $totalAtt) * 100) : null;

        // Marks grouped by exam
        $marksByExam = $student->marks
            ->where('status', 'published')
            ->groupBy(fn($m) => $m->exam?->name ?? 'Unknown');

        // Average marks
        $publishedMarks = $student->marks->where('status', 'published');
        $avgMarks = $publishedMarks->count() > 0
            ? round($publishedMarks->avg(fn($m) => ($m->theory ?? 0) + ($m->practical ?? 0)), 1)
            : null;

        return view('parent.child-overview', compact(
            'student', 'totalAtt', 'present', 'absent', 'late',
            'attendancePct', 'marksByExam', 'avgMarks'
        ));
    }
}
