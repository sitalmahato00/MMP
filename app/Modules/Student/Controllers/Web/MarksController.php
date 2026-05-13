<?php

namespace App\Modules\Student\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\Mark;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
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

        $examType = $request->get('exam_type');
        $category = $request->get('category');
        $semester = $request->get('semester');

        $filteredMarks = Mark::with(['exam', 'subject'])
            ->where('student_id', $student->id)
            ->visibleToPortal();

        if ($examType) {
            $filteredMarks->whereHas('exam', function ($query) use ($examType) {
                $query->where('type', $examType);
            });
        }

        if ($category) {
            $filteredMarks->whereHas('exam', function ($query) use ($category) {
                $query->where('category', $category);
            });
        }

        if ($semester) {
            $filteredMarks->where('semester', $semester);
        }

        $filteredMarks = $filteredMarks->latest()->get();
        $assessmentResults = $this->studentRecordService->buildAssessmentResults($filteredMarks);

        $allMarks = $this->studentRecordService->getVisiblePublishedMarks($student);
        $marksSummary = $this->studentRecordService->summarizeMarks($allMarks);

        $totalAssessments = (int) ($marksSummary['total_assessments'] ?? 0);
        $averagePercentage = (float) ($marksSummary['percentage_rate'] ?? 0);
        $totalSubjects = (int) ($marksSummary['total_subjects'] ?? 0);

        $passedSubjects = $allMarks->filter(fn (Mark $mark) => $mark->is_passed)->count();
        $passPercentage = $allMarks->count() > 0
            ? round(($passedSubjects / $allMarks->count()) * 100, 1)
            : 0;

        return view('student.marks.index', compact(
            'student',
            'assessmentResults',
            'totalAssessments',
            'averagePercentage',
            'totalSubjects',
            'passPercentage'
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
