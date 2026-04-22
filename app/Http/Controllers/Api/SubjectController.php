<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function students(Subject $subject)
    {
        $students = $subject->program->students()
            ->where('semester', $subject->semester)
            ->with(['user:id,name,email'])
            ->get(['id', 'user_id', 'student_no', 'program_id', 'semester']);

        return response()->json($students);
    }
}
