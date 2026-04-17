<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
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
        $students = Student::query()
            ->where('program_id', $program->id)
            ->where('roll_number', 'like', 'DIT-081-%')
            ->orderBy('roll_number')
            ->take(4)
            ->get()
            ->all();

        if (count($students) < 2) {
            $students = [$studentOne, $studentTwo];
        }

        $demo->seedTimetableAndAttendance($session, $program, $teacher, $subjectOne, $subjectTwo, $subjectThree, $students);

        $exam = $demo->seedExam($session, $department, $program);
        $demo->seedMarks($exam, $teacher, $students, $subjectOne, $subjectTwo, $subjectThree);
        $demo->seedAssignments($teacher, $program, $subjectTwo, $studentOne, $studentTwo, $assets);
        $demo->seedAuditLog(
            User::where('email', 'principal@mmp.edu.np')->firstOrFail(),
            $department,
            $program,
            $exam
        );
    }
}
