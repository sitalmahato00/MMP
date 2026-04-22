<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\Mark;
use Illuminate\Http\Request;

class ExamsController extends Controller
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

        return view('teacher.exams.index', compact('exams', 'totalExams', 'upcomingExams', 'ongoingExams', 'completedExams', 'session'));
    }

    public function show(Exam $exam, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher teaches a subject in this exam
        $hasAccess = $exam->subjects()->whereHas('teachers', fn ($q) => $q->where('teachers.id', $teacher->id)
            ->where('subject_teacher.academic_session_id', $session?->id))->exists();

        if (!$hasAccess) {
            abort(403, 'Unauthorized');
        }

        // Get marks for this exam
        $marks = Mark::where('exam_id', $exam->id)
            ->where('teacher_id', $teacher->id)
            ->with(['student.user:id,name', 'subject'])
            ->paginate(50);

        // Get statistics
        $totalMarks = (clone Mark::where('exam_id', $exam->id)->where('teacher_id', $teacher->id))->count();
        $submittedMarks = (clone Mark::where('exam_id', $exam->id)->where('teacher_id', $teacher->id)->where('status', '!=', 'draft'))->count();
        $publishedMarks = (clone Mark::where('exam_id', $exam->id)->where('teacher_id', $teacher->id)->where('status', 'published'))->count();

        return view('teacher.exams.show', compact('exam', 'marks', 'totalMarks', 'submittedMarks', 'publishedMarks', 'session'));
    }
}
