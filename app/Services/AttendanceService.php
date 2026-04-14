<?php

namespace App\Services;

use App\Models\{Student, AttendanceSession, Attendance};
use Illuminate\Support\Facades\Cache;

/**
 * AttendanceService — Class-level and student-level attendance logic.
 */
class AttendanceService
{
    /**
     * Get attendance percentage for a student in a session/subject.
     * Cached per student per subject.
     */
    public function getStudentAttendanceStats(Student $student, ?int $subjectId = null): array
    {
        $cacheKey = "attendance:student:{$student->id}:subject:{$subjectId}";

        return Cache::remember($cacheKey, 600, function () use ($student, $subjectId) {
            $query = Attendance::where('student_id', $student->id)
                ->whereHas('attendanceSession', function ($q) use ($subjectId) {
                    if ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    }
                });

            $total = $query->count();
            $present = $query->where('status', 'present')->count();
            $absent = $query->where('status', 'absent')->count();
            $late = $query->where('status', 'late')->count();

            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            return [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'percentage' => $percentage,
                'is_low' => $percentage < 75,
            ];
        });
    }

    /**
     * Mark bulk attendance for an entire class.
     */
    public function markBulk(AttendanceSession $session, array $records): int
    {
        $count = 0;
        foreach ($records as $studentId => $status) {
            Attendance::updateOrCreate(
                ['attendance_session_id' => $session->id, 'student_id' => $studentId],
                ['status' => $status]
            );
            $count++;
        }

        // Bust attendance cache for all students in this session
        foreach (array_keys($records) as $studentId) {
            Cache::forget("attendance:student:{$studentId}:subject:{$session->subject_id}");
            Cache::forget("attendance:student:{$studentId}:subject:null");
        }

        return $count;
    }
}
