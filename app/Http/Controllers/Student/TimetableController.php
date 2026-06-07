<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Debug information (only for development)
        if ($request->has('debug')) {
            $debug = [
                'student_id' => $student->id,
                'student_semester' => $student->current_semester,
                'student_program_id' => $student->program_id,
                'program_name' => $student->program->name ?? 'N/A',
            ];
            
            $allTimetables = Timetable::where('program_id', $student->program_id)
                ->with('academicSession')
                ->get()
                ->map(function($tt) {
                    return [
                        'id' => $tt->id,
                        'semester' => $tt->semester,
                        'section' => $tt->section,
                        'is_active' => $tt->is_active,
                        'academic_session' => $tt->academicSession->name ?? 'N/A',
                        'slots_count' => $tt->slots()->count()
                    ];
                });
            
            return response()->json([
                'debug' => $debug,
                'all_timetables' => $allTimetables
            ]);
        }

        // Get timetable for student's program and semester ONLY
        $timetable = Timetable::with(['slots.subject', 'slots.teacher.user', 'academicSession'])
            ->where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->where('is_active', true)
            ->first();

        if (!$timetable) {
            return view('student.timetable.index', [
                'student' => $student,
                'timetable' => null,
                'slots' => collect([]),
                'subjects' => collect([]),
                'teachers' => collect([]),
                'subjectAttendance' => collect([])
            ]);
        }

        $slots = $timetable->slots()
            ->with(['subject', 'teacher.user'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Get unique subjects and teachers from slots for the timetable-grid component
        $subjects = $slots->pluck('subject')->filter()->unique('id');
        $teachers = $slots->pluck('teacher')->filter()->unique('id');

        // Get subject-wise attendance data
        $subjectAttendance = $this->getSubjectWiseAttendance($student);

        return view('student.timetable.index', compact('student', 'timetable', 'slots', 'subjects', 'teachers', 'subjectAttendance'));
    }

    /**
     * Get subject-wise attendance data for the student
     */
    private function getSubjectWiseAttendance($student)
    {
        // Get all subjects for the student's current semester
        $subjects = $student->program->subjects()
            ->where('semester', $student->current_semester)
            ->get();

        $attendanceData = [];

        foreach ($subjects as $subject) {
            // Get attendance sessions for this subject in the student's program and semester
            $sessions = AttendanceSession::where('subject_id', $subject->id)
                ->where('program_id', $student->program_id)
                ->where('semester', $student->current_semester)
                ->when($student->section, fn($q) => $q->where('section', $student->section))
                ->get();

            $totalSessions = $sessions->count();
            
            if ($totalSessions > 0) {
                // Get student's attendance for these sessions
                $attendances = Attendance::where('student_id', $student->id)
                    ->whereIn('attendance_session_id', $sessions->pluck('id'))
                    ->get();

                $presentCount = $attendances->where('status', 'present')->count();
                $absentCount = $attendances->where('status', 'absent')->count();
                $lateCount = $attendances->where('status', 'late')->count();
                
                $attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 1) : 0;

                $attendanceData[] = [
                    'subject' => $subject,
                    'total' => $totalSessions,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'rate' => $attendanceRate,
                    'last_class' => $sessions->sortByDesc('date')->first()?->date
                ];
            }
        }

        return collect($attendanceData)->sortBy('subject.name');
    }
}
