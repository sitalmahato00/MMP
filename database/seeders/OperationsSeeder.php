<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class OperationsSeeder extends Seeder
{
    public function run(): void
    {
        $demo = new DemoDataSeeder();
        $assets = $demo->seedAssets();

        $session = AcademicSession::where('name', '2081-2082')->firstOrFail();
        $department = Department::where('code', 'IT')->firstOrFail();
        $program = Program::where('code', 'DIT')->firstOrFail();
        $teacher = Teacher::where('employee_id', 'T-001')->firstOrFail();
        $subjectOne = Subject::where('code', 'CG501')->firstOrFail();
        $subjectTwo = Subject::where('code', 'WT502')->firstOrFail();
        $subjectThree = Subject::where('code', 'DBMS503')->firstOrFail();
        $studentOne = Student::where('roll_number', 'DIT-081-01')->firstOrFail();
        $studentTwo = Student::where('roll_number', 'DIT-081-02')->firstOrFail();

        $demo->seedTimetableAndAttendance($session, $program, $teacher, $subjectOne, $subjectTwo, $studentOne, $studentTwo);

        $exam = $demo->seedExam($session, $department, $program);
        $demo->seedMarks($exam, $teacher, $studentOne, $studentTwo, $subjectOne, $subjectTwo, $subjectThree);
        $demo->seedAssignments($teacher, $program, $subjectTwo, $studentOne, $studentTwo, $assets);
        $demo->seedAuditLog(
            \App\Models\User::where('email', 'principal@mmp.edu.np')->firstOrFail(),
            $department,
            $program,
            $exam
        );
    }
}
