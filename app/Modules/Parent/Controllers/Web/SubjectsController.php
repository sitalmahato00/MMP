<?php

namespace App\Modules\Parent\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Department\Models\Department;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use App\Services\StudentRecordService;

class SubjectsController extends Controller
{
    public function __construct(private readonly StudentRecordService $studentRecordService)
    {
    }

    public function index()
    {
        $parent = auth()->user()->parentProfile;

        if (!$parent) {
            abort(403, 'Parent profile not found');
        }

        $session = AcademicSession::current();
        $childrenSubjects = $parent->children()
            ->with(['user', 'department', 'program'])
            ->get()
            ->map(function ($student) use ($session) {
                return [
                    'student' => $student,
                    'subjectOverview' => $this->studentRecordService->getProgramSubjectOverview($student, $session),
                ];
            });

        return view('parent.subjects.index', compact('parent', 'session', 'childrenSubjects'));
    }
}
