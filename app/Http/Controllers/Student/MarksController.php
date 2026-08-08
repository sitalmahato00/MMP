<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use App\Services\StudentRecordService;
use Illuminate\Http\Request;

class MarksController extends Controller
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

        // Semester selector
        $currentSemester  = $student->current_semester;
        $selectedSemester = $request->get('semester') ? (int) $request->get('semester') : null;
        if ($selectedSemester !== null) {
            $selectedSemester = max(1, min($selectedSemester, $currentSemester));
        }
        $semesterOptions = range(1, $currentSemester);

        $examType = $request->get('exam_type');
        $category = $request->get('category');

        $filteredMarks = Mark::with(['exam', 'subject'])
            ->where('student_id', $student->id)
            ->visibleToPortal();

        if ($examType) {
            $filteredMarks->whereHas('exam', fn ($q) => $q->where('type', $examType));
        }

        if ($category) {
            $filteredMarks->whereHas('exam', fn ($q) => $q->where('category', $category));
        }

        // Filter by semester via subject
        if ($selectedSemester !== null) {
            $filteredMarks->whereHas('subject', fn ($q) => $q->where('semester', $selectedSemester));
        }

        $filteredMarks    = $filteredMarks->latest()->get();
        $assessmentResults = $this->studentRecordService->buildAssessmentResults($filteredMarks);

        // Overall stats use all marks (not semester-filtered)
        $allMarks       = $this->studentRecordService->getVisiblePublishedMarks($student);
        $marksSummary   = $this->studentRecordService->summarizeMarks($allMarks);

        $totalAssessments  = (int) ($marksSummary['total_assessments'] ?? 0);
        $averagePercentage = (float) ($marksSummary['percentage_rate'] ?? 0);
        $totalSubjects     = (int) ($marksSummary['total_subjects'] ?? 0);

        $passedSubjects  = $allMarks->filter(fn (Mark $mark) => $mark->is_passed)->count();
        $passPercentage  = $allMarks->count() > 0
            ? round(($passedSubjects / $allMarks->count()) * 100, 1)
            : 0;

        return view('student.marks.index', compact(
            'student', 'assessmentResults', 'totalAssessments',
            'averagePercentage', 'totalSubjects', 'passPercentage',
            'selectedSemester', 'currentSemester', 'semesterOptions'
        ));
    }

    public function show($id)
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $exam = Exam::with(['programs', 'academicSession', 'department'])->findOrFail($id);

        if (!$exam->isPublishedState) {
            abort(404, 'Exam results not published');
        }

        $marksheet = $this->studentRecordService->getPublishedMarksheet($student, $exam);

        if ($marksheet['marksData']->isEmpty()) {
            abort(404, 'No results found');
        }

        $student->load(['user', 'department', 'program', 'academicSession']);

        return view('student.marks.show', array_merge(
            compact('student', 'exam'),
            $marksheet
        ));
    }
}
