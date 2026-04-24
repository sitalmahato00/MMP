<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $parent = Auth::user()->parentProfile;
        $children = $parent?->children()
            ->with(['user', 'department', 'program', 'attendances' => fn($q) => $q->latest()])
            ->get() ?? collect();

        $session = AcademicSession::current();

        $childrenData = $children->map(function ($student) use ($session) {
            $total = $student->attendances->count();
            $present = $student->attendances->where('status', 'present')->count();
            $absent = $student->attendances->where('status', 'absent')->count();
            $late = $student->attendances->where('status', 'late')->count();
            $pct = $total > 0 ? round(($present / $total) * 100, 1) : null;

            // Subject-wise attendance with class/lab breakdown
            $subjectAttendance = $student->attendances()
                ->with(['attendanceSession.subject:id,name,code,type'])
                ->whereHas('attendanceSession', function($q) use ($session) {
                    if ($session) {
                        $q->where('academic_session_id', $session->id);
                    }
                })
                ->get()
                ->groupBy(function($attendance) {
                    return $attendance->attendanceSession->subject_id;
                })
                ->map(function($subjectAttendances) {
                    $subject = $subjectAttendances->first()->attendanceSession->subject;

                    [$classAttendances, $labAttendances] = $this->splitAttendanceBuckets($subjectAttendances, $subject->type);
                    
                    $classTotal = $classAttendances->count();
                    $classPresent = $classAttendances->where('status', 'present')->count();
                    $classPct = $classTotal > 0 ? round(($classPresent / $classTotal) * 100) : 0;
                    
                    $labTotal = $labAttendances->count();
                    $labPresent = $labAttendances->where('status', 'present')->count();
                    $labPct = $labTotal > 0 ? round(($labPresent / $labTotal) * 100) : 0;
                    
                    return [
                        'subject_name' => $subject->name,
                        'subject_code' => $subject->code,
                        'subject_type' => $subject->type,
                        'class_percentage' => $classPct,
                        'class_total' => $classTotal,
                        'class_present' => $classPresent,
                        'lab_percentage' => $labPct,
                        'lab_total' => $labTotal,
                        'lab_present' => $labPresent,
                        'has_lab' => in_array($subject->type, ['practical', 'both']),
                    ];
                })
                ->values();

            return [
                'student' => $student,
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'pct' => $pct,
                'subjectAttendance' => $subjectAttendance,
                'recentRecords' => $student->attendances->take(15),
            ];
        });

        return view('parent.attendance', compact('childrenData'));
    }

    private function splitAttendanceBuckets($subjectAttendances, ?string $subjectType): array
    {
        $labAttendances = $subjectAttendances->filter(function ($attendance) use ($subjectType) {
            return $this->isLabSession($attendance->attendanceSession->period ?? null, $subjectType);
        })->values();

        $classAttendances = $subjectAttendances->reject(function ($attendance) use ($subjectType) {
            return $this->isLabSession($attendance->attendanceSession->period ?? null, $subjectType);
        })->values();

        return [$classAttendances, $labAttendances];
    }

    private function isLabSession(?string $period, ?string $subjectType): bool
    {
        $normalizedPeriod = strtolower(trim((string) $period));
        $normalizedType = strtolower(trim((string) $subjectType));

        if (str_contains($normalizedPeriod, 'lab')) {
            return true;
        }

        if (str_contains($normalizedPeriod, 'class')) {
            return false;
        }

        return $normalizedType === 'practical';
    }
}
