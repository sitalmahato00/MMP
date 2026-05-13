<?php

namespace App\Modules\Student\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\CMS\Models\Download;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use App\Services\StudentRecordService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected StudentRecordService $studentRecordService;

    public function __construct(StudentRecordService $studentRecordService)
    {
        $this->studentRecordService = $studentRecordService;
    }

    public function index(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $subjectId = $request->get('subject_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $bounds = $this->studentRecordService->getAttendanceDateBounds($student);
        $defaultFromDate = $bounds['first_date'];
        $defaultToDate = $bounds['last_date'];

        if ($fromDate) {
            try {
                $adFromDate = adDate($fromDate);
                $startDate = $adFromDate ? $adFromDate->startOfDay() : $defaultFromDate;
                $displayFromDate = $fromDate;
            } catch (\Exception $e) {
                $startDate = $defaultFromDate;
                $displayFromDate = $defaultFromDate ? bsDate($defaultFromDate, 'Y-m-d') : '';
            }
        } else {
            $startDate = $defaultFromDate;
            $displayFromDate = $defaultFromDate ? bsDate($defaultFromDate, 'Y-m-d') : '';
        }

        if ($toDate) {
            try {
                $adToDate = adDate($toDate);
                $endDate = $adToDate ? $adToDate->endOfDay() : $defaultToDate;
                $displayToDate = $toDate;
            } catch (\Exception $e) {
                $endDate = $defaultToDate;
                $displayToDate = $defaultToDate ? bsDate($defaultToDate, 'Y-m-d') : '';
            }
        } else {
            $endDate = $defaultToDate;
            $displayToDate = $defaultToDate ? bsDate($defaultToDate, 'Y-m-d') : '';
        }

        $attendances = $this->studentRecordService->getAttendanceRecords(
            $student,
            $startDate,
            $endDate,
            $subjectId ? (int) $subjectId : null
        );

        $attendanceStats = $this->studentRecordService->summarizeAttendance($attendances);
        $totalClasses = $attendanceStats['total'];
        $presentCount = $attendanceStats['present'];
        $absentCount = $attendanceStats['absent'];
        $lateCount = $attendanceStats['late'];
        $attendanceRate = $attendanceStats['rate'];

        $subjectWise = $attendances
            ->filter(fn ($attendance) => $attendance->attendanceSession?->subject)
            ->groupBy(fn ($attendance) => $attendance->attendanceSession->subject_id)
            ->map(function ($group) {
                $total = $group->count();
                $present = $group->where('status', 'present')->count();
                $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

                return [
                    'subject' => $group->first()->attendanceSession->subject,
                    'total' => $total,
                    'present' => $present,
                    'absent' => $group->where('status', 'absent')->count(),
                    'late' => $group->where('status', 'late')->count(),
                    'rate' => $rate,
                ];
            })
            ->values();

        $subjects = Subject::where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->orderBy('name')
            ->get();

        $calendar = $attendances
            ->filter(fn ($attendance) => $attendance->attendanceSession?->date)
            ->groupBy(fn ($attendance) => $attendance->attendanceSession->date->format('Y-m-d'))
            ->map(function ($dayAttendances) {
                $present = $dayAttendances->where('status', 'present')->count();
                $total = $dayAttendances->count();

                return [
                    'date' => $dayAttendances->first()->attendanceSession->date,
                    'total' => $total,
                    'present' => $present,
                    'absent' => $dayAttendances->where('status', 'absent')->count(),
                    'late' => $dayAttendances->where('status', 'late')->count(),
                    'status' => $present === $total ? 'full' : ($present > 0 ? 'partial' : 'absent'),
                ];
            });

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

    private function exportAttendance($student, $attendances, $subjectWise, $attendanceRate)
    {
        $data = [
            'student' => $student,
            'attendances' => $attendances,
            'subjectWise' => $subjectWise,
            'attendanceRate' => $attendanceRate,
            'exportDate' => bsDate(now(), 'Y F d, l'),
        ];

        $pdf = Pdf::loadView('student.attendance.export', $data);

        return $pdf->download('attendance-report-' . $student->student_no . '.pdf');
    }
}
