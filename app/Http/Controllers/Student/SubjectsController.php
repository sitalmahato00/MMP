<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Services\StudentRecordService;

class SubjectsController extends Controller
{
    public function __construct(private readonly StudentRecordService $studentRecordService)
    {
    }

    public function index()
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $session = AcademicSession::current();
        $subjectOverview = $this->studentRecordService->getProgramSubjectOverview($student, $session);

        return view('student.subjects.index', compact('student', 'session', 'subjectOverview'));
    }
}
