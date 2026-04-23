<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Subject;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Get filters
        $subjectId = $request->get('subject_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        // Default date range - current month
        $defaultFromDate = now()->startOfMonth();
        $defaultToDate = now();
        
        // Convert BS dates to AD for filtering
        if ($fromDate) {
            try {
                $adFromDate = adDate($fromDate);
                $startDate = $adFromDate ?: $defaultFromDate;
                $displayFromDate = $fromDate;
            } catch (\Exception $e) {
                $startDate = $defaultFromDate;
                $displayFromDate = bsDate($defaultFromDate, 'Y-m-d');
            }
        } else {
            $startDate = $defaultFromDate;
            $displayFromDate = bsDate($defaultFromDate, 'Y-m-d');
        }
        
        if ($toDate) {
            try {
                $adToDate = adDate($toDate);
                $endDate = $adToDate ? $adToDate->endOfDay() : $defaultToDate;
                $displayToDate = $toDate;
            } catch (\Exception $e) {
                $endDate = $defaultToDate;
                $displayToDate = bsDate($defaultToDate, 'Y-m-d');
            }
        } else {
            $endDate = $defaultToDate;
            $displayToDate = bsDate($defaultToDate, 'Y-m-d');
        }

        // Get attendance records
        $attendanceQuery = Attendance::with(['attendanceSession.subject', 'attendanceSession.teacher'])
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            });

        if ($subjectId) {
            $attendanceQuery->whereHas('attendanceSession', function($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        $attendances = $attendanceQuery->get();

        // Calculate statistics
        $totalClasses = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        
        $attendanceRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 1) : 0;

        // Get subject-wise breakdown
        $subjectWise = Attendance::with('attendanceSession.subject')
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->get()
            ->groupBy('attendanceSession.subject_id')
            ->map(function($group) {
                $total = $group->count();
                $present = $group->where('status', 'present')->count();
                $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                
                return [
                    'subject' => $group->first()->attendanceSession->subject,
                    'total' => $total,
                    'present' => $present,
                    'absent' => $group->where('status', 'absent')->count(),
                    'late' => $group->where('status', 'late')->count(),
                    'rate' => $rate
                ];
            });

        // Get subjects for filter
        $subjects = Subject::where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->orderBy('name')
            ->get();

        // Calendar data - group by date
        $calendar = $attendances->groupBy(function($attendance) {
            return $attendance->attendanceSession->date->format('Y-m-d');
        })->map(function($dayAttendances) {
            $present = $dayAttendances->where('status', 'present')->count();
            $total = $dayAttendances->count();
            
            return [
                'date' => $dayAttendances->first()->attendanceSession->date,
                'total' => $total,
                'present' => $present,
                'absent' => $dayAttendances->where('status', 'absent')->count(),
                'late' => $dayAttendances->where('status', 'late')->count(),
                'status' => $present == $total ? 'full' : ($present > 0 ? 'partial' : 'absent')
            ];
        });

        // Handle export request
        if ($request->has('export')) {
            return $this->exportAttendance($student, $attendances, $subjectWise, $attendanceRate);
        }

        return view('student.attendance.index', compact(
            'student',
            'attendances',
            'totalClasses',
            'presentCount',
            'absentCount',
            'lateCount',
            'attendanceRate',
            'subjectWise',
            'subjects',
            'calendar',
            'displayFromDate',
            'displayToDate'
        ));
    }

    /**
     * Export attendance data as PDF
     */
    private function exportAttendance($student, $attendances, $subjectWise, $attendanceRate)
    {
        $data = [
            'student' => $student,
            'attendances' => $attendances,
            'subjectWise' => $subjectWise,
            'attendanceRate' => $attendanceRate,
            'exportDate' => bsDate(now(), 'Y F d, l')
        ];

        $pdf = Pdf::loadView('student.attendance.export', $data);
        
        return $pdf->download('attendance-report-' . $student->student_no . '.pdf');
    }
}
