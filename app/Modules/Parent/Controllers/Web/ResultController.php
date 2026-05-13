<?php

namespace App\Modules\Parent\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Exam;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use App\Services\StudentRecordService;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function __construct(protected StudentRecordService $studentRecordService)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $parent = $user->parentProfile;
        
        if (!$parent) {
            abort(403, 'Parent profile not found');
        }
        
        $children = $parent->children()
            ->with(['user', 'department', 'program'])
            ->get();

        $childrenResults = $children->map(function (Student $child) {
            $publishedMarks = $this->studentRecordService->getVisiblePublishedMarks($child, [
                'exam.academicSession',
                'exam.department',
            ]);

            $assessmentResults = $this->studentRecordService->buildAssessmentResults($publishedMarks);

            return [
                'child' => $child,
                'assessment_results' => $assessmentResults,
                'total_assessments' => $assessmentResults->count(),
            ];
        })->all();

        return view('parent.results', compact('childrenResults'));
    }

    public function show(Student $student, Exam $exam)
    {
        $user = Auth::user();
        $parent = $user->parentProfile;
        
        if (!$parent) {
            abort(403, 'Parent profile not found');
        }
        
        // Verify this student belongs to this parent
        if (!$parent->children()->where('students.id', $student->id)->exists()) {
            abort(403, 'Unauthorized access');
        }
        
        // Verify exam is published
        if (!$exam->isPublishedState) {
            abort(404, 'Exam results not published');
        }

        $marksheet = $this->studentRecordService->getPublishedMarksheet($student, $exam);

        if ($marksheet['marksData']->isEmpty()) {
            abort(404, 'No results found');
        }

        $student->load(['user', 'department', 'program', 'academicSession']);
        $exam->load(['academicSession', 'department']);

        return view('parent.results-show', array_merge(
            compact('student', 'exam'),
            $marksheet
        ));
    }
}
