<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Subject;
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

        $exams = $query->latest('start_date')->paginate(20)->withQueryString();

        // Stats
        $totalExams = (clone $query)->count();
        $upcomingExams = (clone $query)->where('status', 'upcoming')->count();
        $ongoingExams = (clone $query)->where('status', 'ongoing')->count();
        $completedExams = (clone $query)->where('status', 'completed')->count();

        // Mark assigned subjects for the teacher
        foreach ($exams as $exam) {
            foreach ($exam->subjects as $subject) {
                $subject->is_assigned_to_teacher = $subject->teachers()
                    ->where('teachers.id', $teacher->id)
                    ->where('subject_teacher.academic_session_id', $session?->id)
                    ->exists();
            }
        }

        return view('teacher.exams.index', compact('exams', 'totalExams', 'upcomingExams', 'ongoingExams', 'completedExams', 'session'));
    }

    public function show(Exam $exam, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher teaches a subject in this exam
        $teacherSubjects = $exam->subjects()
            ->whereHas('teachers', fn ($q) => $q->where('teachers.id', $teacher->id)
                ->where('subject_teacher.academic_session_id', $session?->id))
            ->get();

        if ($teacherSubjects->isEmpty()) {
            abort(403, 'Unauthorized');
        }

        // Get marks for this exam (only for teacher's subjects)
        $marks = Mark::where('exam_id', $exam->id)
            ->whereIn('subject_id', $teacherSubjects->pluck('id'))
            ->with(['student.user:id,name', 'subject:id,name,code'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('student.user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
            })
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->paginate(50)
            ->withQueryString();

        // Get statistics (only for teacher's subjects)
        $totalMarks = Mark::where('exam_id', $exam->id)
            ->whereIn('subject_id', $teacherSubjects->pluck('id'))
            ->count();
        $draftMarks = Mark::where('exam_id', $exam->id)
            ->whereIn('subject_id', $teacherSubjects->pluck('id'))
            ->where('status', 'draft')
            ->count();
        $submittedMarks = Mark::where('exam_id', $exam->id)
            ->whereIn('subject_id', $teacherSubjects->pluck('id'))
            ->where('status', 'submitted')
            ->count();
        $approvedMarks = Mark::where('exam_id', $exam->id)
            ->whereIn('subject_id', $teacherSubjects->pluck('id'))
            ->where('status', 'approved')
            ->count();

        return view('teacher.exams.show', compact(
            'exam', 'marks', 'teacherSubjects', 'session',
            'totalMarks', 'draftMarks', 'submittedMarks', 'approvedMarks'
        ));
    }

    public function fillMarks(Exam $exam, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Get teacher's subjects for this exam
        $teacherSubjects = $exam->subjects()
            ->whereHas('teachers', fn ($q) => $q->where('teachers.id', $teacher->id)
                ->where('subject_teacher.academic_session_id', $session?->id))
            ->get();

        if ($teacherSubjects->isEmpty()) {
            abort(403, 'Unauthorized');
        }

        $subjectId = $request->subject_id ?? $teacherSubjects->first()->id;
        $subject = $teacherSubjects->firstWhere('id', $subjectId);

        if (!$subject) {
            abort(403, 'You are not assigned to this subject');
        }

        // Get students for this subject
        $students = $subject->program->students()
            ->where('semester', $subject->semester)
            ->where('status', 'active')
            ->with(['user:id,name,email'])
            ->orderBy('roll_number')
            ->get();

        // Get existing marks
        $existingMarks = Mark::where('exam_id', $exam->id)
            ->where('subject_id', $subject->id)
            ->get()
            ->keyBy('student_id');

        // Get marking scheme
        $scheme = $exam->markingSchemes()
            ->where('subject_id', $subject->id)
            ->first() ?? $subject;

        return view('teacher.exams.fill-marks', compact(
            'exam', 'subject', 'students', 'existingMarks', 'scheme', 'session', 'teacherSubjects'
        ));
    }

    public function saveMarks(Exam $exam, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.is_absent' => 'nullable|boolean',
            'marks.*.assessment_obtained_marks' => 'nullable|numeric|min:0',
            'marks.*.internal_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.external_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.internal_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.external_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.remarks' => 'nullable|string|max:500',
            'action' => 'required|in:draft,submit',
        ]);

        // Verify teacher teaches this subject
        $subject = Subject::findOrFail($validated['subject_id']);
        if (!$teacher->subjects()->where('subject_id', $subject->id)->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        foreach ($validated['marks'] as $markData) {
            $isAbsent = $markData['is_absent'] ?? false;

            $data = [
                'exam_id' => $exam->id,
                'student_id' => $markData['student_id'],
                'subject_id' => $subject->id,
                'program_id' => $subject->program_id,
                'semester' => $subject->semester,
                'is_absent' => $isAbsent,
                'status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
                'remarks' => $markData['remarks'] ?? null,
            ];

            if ($exam->category === 'monthly_assessment') {
                $data['assessment_full_marks'] = $exam->assessment_full_marks ?? 100;
                $data['assessment_pass_marks'] = $exam->assessment_pass_marks ?? 40;
                $data['assessment_obtained_marks'] = $isAbsent ? null : ($markData['assessment_obtained_marks'] ?? null);
            } else {
                $data['internal_theory_marks'] = $isAbsent ? null : ($markData['internal_theory_marks'] ?? null);
                $data['external_theory_marks'] = $isAbsent ? null : ($markData['external_theory_marks'] ?? null);
                $data['internal_practical_marks'] = $isAbsent ? null : ($markData['internal_practical_marks'] ?? null);
                $data['external_practical_marks'] = $isAbsent ? null : ($markData['external_practical_marks'] ?? null);
            }

            Mark::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $markData['student_id'],
                    'subject_id' => $subject->id,
                ],
                $data
            );
        }

        $message = $validated['action'] === 'submit' ? 'Marks submitted successfully.' : 'Marks saved as draft.';
        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', $message);
    }
}
