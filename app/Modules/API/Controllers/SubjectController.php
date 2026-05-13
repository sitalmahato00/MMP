<?php

namespace App\Modules\Api\Controllers;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function students(Subject $subject)
    {
        $students = $subject->program->students()
            ->where('current_semester', $subject->semester)
            ->with(['user:id,name,email'])
            ->get(['id', 'user_id', 'student_no', 'program_id', 'current_semester']);

        return response()->json($students);
    }
}
