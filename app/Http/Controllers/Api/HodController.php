<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HodController extends Controller
{
    /**
     * HOD Dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $hod = Teacher::where('user_id', $user->id)->firstOrFail();
            $department = $hod->department;

            $totalStudents = Student::where('department_id', $department->id)->count();
            $totalTeachers = Teacher::where('department_id', $department->id)->count();
            $totalSubjects = Subject::where('department_id', $department->id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'department' => $department->name,
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_subjects' => $totalSubjects,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Department Overview
     */
    public function departmentOverview(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $hod = Teacher::where('user_id', $user->id)->firstOrFail();
            $department = $hod->department;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'head' => $department->head?->user?->name,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch department overview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Department Statistics
     */
    public function departmentStatistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $hod = Teacher::where('user_id', $user->id)->firstOrFail();
            $department = $hod->department;

            $students = Student::where('department_id', $department->id)->get();
            $avgAttendance = 0;
            $avgMarks = 0;

            if ($students->count() > 0) {
                $totalAttendance = 0;
                foreach ($students as $student) {
                    $records = Attendance::where('student_id', $student->id)->get();
                    if ($records->count() > 0) {
                        $present = $records->where('status', 'present')->count();
                        $totalAttendance += ($present / $records->count()) * 100;
                    }
                }
                $avgAttendance = $students->count() > 0 ? $totalAttendance / $students->count() : 0;

                $marks = Mark::whereIn('student_id', $students->pluck('id'))->get();
                $avgMarks = $marks->count() > 0 ? $marks->avg('obtained_marks') : 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'average_attendance' => round($avgAttendance, 2),
                    'average_marks' => round($avgMarks, 2),
                    'total_students' => $students->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Students
     */
    public function students(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $hod = Teacher::where('user_id', $user->id)->firstOrFail();

            $students = Student::where('department_id', $hod->department_id)
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $students->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->user?->name,
                    'email' => $s->user?->email,
                    'roll_number' => $s->roll_number,
                    'program' => $s->program?->name,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Student Detail
     */
    public function studentDetail(Request $request, Student $student): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'email' => $student->user?->email,
                    'phone' => $student->user?->phone,
                    'roll_number' => $student->roll_number,
                    'program' => $student->program?->name,
                    'semester' => $student->semester,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Student Attendance
     */
    public function studentAttendance(Request $request, Student $student): JsonResponse
    {
        try {
            $records = Attendance::where('student_id', $student->id)->get();
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $percentage = $total > 0 ? ($present / $total) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_classes' => $total,
                    'present' => $present,
                    'absent' => $records->where('status', 'absent')->count(),
                    'attendance_percentage' => round($percentage, 2),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Student Marks
     */
    public function studentMarks(Request $request, Student $student): JsonResponse
    {
        try {
            $marks = Mark::where('student_id', $student->id)->get();
            $average = $marks->count() > 0 ? $marks->avg('obtained_marks') : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'average_marks' => round($average, 2),
                    'total_exams' => $marks->count(),
                    'marks' => $marks->map(fn($m) => [
                        'subject' => $m->subject?->name,
                        'obtained_marks' => $m->obtained_marks,
                    ])
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch marks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Teachers
     */
    public function teachers(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $hod = Teacher::where('user_id', $user->id)->firstOrFail();

            $teachers = Teacher::where('department_id', $hod->department_id)
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $teachers->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->user?->name,
                    'email' => $t->user?->email,
                    'phone' => $t->user?->phone,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teachers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Teacher Detail
     */
    public function teacherDetail(Request $request, Teacher $teacher): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $teacher->id,
                    'name' => $teacher->user?->name,
                    'email' => $teacher->user?->email,
                    'phone' => $teacher->user?->phone,
                    'department' => $teacher->department?->name,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teacher detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Teacher Subjects
     */
    public function teacherSubjects(Request $request, Teacher $teacher): JsonResponse
    {
        try {
            $subjects = $teacher->subjects ?? collect();

            return response()->json([
                'success' => true,
                'data' => $subjects->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Subjects
     */
    public function subjects(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $hod = Teacher::where('user_id', $user->id)->firstOrFail();

            $subjects = Subject::where('department_id', $hod->department_id)->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $subjects->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Subject Detail
     */
    public function subjectDetail(Request $request, Subject $subject): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'teacher' => $subject->teacher?->user?->name,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attendance Report
     */
    public function attendanceReport(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'report' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marks Report
     */
    public function marksReport(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'report' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Performance Report
     */
    public function performanceReport(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'report' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assignment Report
     */
    public function assignmentReport(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'report' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Sessions
     */
    public function sessions(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'sessions' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sessions: ' . $e->getMessage(),
            ], 500);
        }
    }
}
