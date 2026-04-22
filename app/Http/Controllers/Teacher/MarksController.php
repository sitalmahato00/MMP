<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get exams for teacher's subjects
        $query = Exam::query()
            ->whereHas('subjects', function ($q) use ($teacher, $session) {
                $q->whereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacher->id)
                    ->where('subject_teacher.academic_session_id', $session?->id));
            })
            ->with(['academicSession:id,name', 'subjects'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->category, fn ($q) => $q->where('category', $request->category));

        $exams = $query->latest('start_date')->paginate(20);

        // Stats
        $totalExams = (clone $query)->count();
        $upcomingExams = (clone $query)->where('status', 'upcoming')->count();
        $ongoingExams = (clone $query)->where('status', 'ongoing')->count();
        $completedExams = (clone $query)->where('status', 'completed')->count();

        return view('teacher.marks.index', compact('exams', 'totalExams', 'upcomingExams', 'ongoingExams', 'completedExams', 'session'));
    }

    public function fillMarks(Exam $exam, Subject $subject, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher teaches this subject
        if (!$teacher->subjects()->where('subject_id', $subject->id)->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        // Get students for this subject
        $students = $subject->program->students()
            ->where('semester', $subject->semester)
            ->with(['user:id,name,email'])
            ->get();

        // Get existing marks
        $marks = Mark::where('exam_id', $exam->id)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', $teacher->id)
            ->get()
            ->keyBy('student_id');

        // Get marking scheme
        $scheme = $exam->markingSchemes()
            ->where('subject_id', $subject->id)
            ->first() ?? $subject;

        return view('teacher.marks.fill', compact('exam', 'subject', 'students', 'marks', 'scheme', 'session'));
    }

    public function saveMarks(Exam $exam, Subject $subject, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher teaches this subject
        if (!$teacher->subjects()->where('subject_id', $subject->id)->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.internal_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.external_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.internal_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.external_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.is_absent' => 'nullable|boolean',
            'marks.*.remarks' => 'nullable|string',
            'action' => 'required|in:draft,submit',
        ]);

        foreach ($data['marks'] as $markData) {
            Mark::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'subject_id' => $subject->id,
                    'student_id' => $markData['student_id'],
                    'teacher_id' => $teacher->id,
                ],
                [
                    'internal_theory_marks' => $markData['internal_theory_marks'] ?? null,
                    'external_theory_marks' => $markData['external_theory_marks'] ?? null,
                    'internal_practical_marks' => $markData['internal_practical_marks'] ?? null,
                    'external_practical_marks' => $markData['external_practical_marks'] ?? null,
                    'is_absent' => $markData['is_absent'] ?? false,
                    'remarks' => $markData['remarks'] ?? null,
                    'status' => $data['action'] === 'submit' ? 'submitted' : 'draft',
                ]
            );
        }

        $message = $data['action'] === 'submit' ? 'Marks submitted successfully.' : 'Marks saved as draft.';
        return redirect()->route('teacher.marks.index')->with('success', $message);
    }

    public function show(Exam $exam, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Get marks for this exam
        $marks = Mark::where('exam_id', $exam->id)
            ->where('teacher_id', $teacher->id)
            ->with(['student.user:id,name', 'subject'])
            ->paginate(50);

        return view('teacher.marks.show', compact('exam', 'marks', 'session'));
    }
}
