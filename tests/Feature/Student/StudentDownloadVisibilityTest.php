<?php

namespace Tests\Feature\Student;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Download;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDownloadVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_sees_resources_matching_scope_and_subject_context(): void
    {
        $session = AcademicSession::create([
            'name' => '2082/83',
            'name_bs' => '2082/83',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(10),
            'is_active' => true,
            'status' => 'active',
            'is_locked' => false,
        ]);

        $department = Department::factory()->create([
            'name' => 'Information Technology',
            'code' => 'DIT',
            'slug' => 'information-technology-main',
        ]);

        $otherDepartment = Department::factory()->create([
            'name' => 'Civil Engineering',
            'code' => 'DCE',
            'slug' => 'civil-engineering-main',
        ]);

        $program = Program::factory()->create([
            'department_id' => $department->id,
            'name' => 'Diploma in Information Technology',
            'code' => 'DIT-PROG',
            'slug' => 'diploma-information-technology-main',
        ]);

        $otherProgram = Program::factory()->create([
            'department_id' => $otherDepartment->id,
            'name' => 'Diploma in Civil Engineering',
            'code' => 'DCE-PROG',
            'slug' => 'diploma-civil-engineering-main',
        ]);

        $teacherUser = User::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $teacherUser->id,
            'department_id' => $department->id,
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'academic_session_id' => $session->id,
            'department_id' => $department->id,
            'program_id' => $program->id,
            'student_no' => 'STU-200',
            'registration_number' => 'REG-200',
            'current_semester' => 4,
            'section' => 'A',
            'batch' => '2082',
            'status' => 'active',
            'is_archived' => false,
        ]);

        $currentSubject = Subject::create([
            'program_id' => $program->id,
            'semester' => 4,
            'name' => 'Database Management System',
            'code' => 'DIT-DBMS-04',
            'type' => 'theory',
        ]);

        $otherSemesterSubject = Subject::create([
            'program_id' => $program->id,
            'semester' => 2,
            'name' => 'Digital Logic',
            'code' => 'DIT-DLG-02',
            'type' => 'theory',
        ]);

        $teacher->subjects()->attach($currentSubject->id, [
            'academic_session_id' => $session->id,
            'section' => 'A',
            'role' => 'lead',
        ]);

        $visibleDownload = Download::create([
            'title' => 'Normalization notes',
            'file_path' => 'downloads/normalization-notes.pdf',
            'file_name' => 'normalization-notes.pdf',
            'category' => 'notes',
            'department_id' => $department->id,
            'program_id' => $program->id,
            'semester' => 4,
            'subject_id' => $currentSubject->id,
            'is_public' => true,
            'visibility' => 'students',
            'uploaded_by' => $teacherUser->id,
        ]);

        $crossScopeTeacherUpload = Download::create([
            'title' => 'Other program resource',
            'file_path' => 'downloads/other-program.pdf',
            'file_name' => 'other-program.pdf',
            'category' => 'notes',
            'department_id' => $otherDepartment->id,
            'program_id' => $otherProgram->id,
            'semester' => 2,
            'is_public' => true,
            'visibility' => 'students',
            'uploaded_by' => $teacherUser->id,
        ]);

        $wrongSubjectSemesterDownload = Download::create([
            'title' => 'Old semester worksheet',
            'file_path' => 'downloads/old-semester.pdf',
            'file_name' => 'old-semester.pdf',
            'category' => 'notes',
            'department_id' => $department->id,
            'program_id' => $program->id,
            'semester' => null,
            'subject_id' => $otherSemesterSubject->id,
            'is_public' => true,
            'visibility' => 'students',
            'uploaded_by' => $teacherUser->id,
        ]);

        $visibleIds = Download::visibleToStudent($student)
            ->pluck('id')
            ->all();

        $this->assertContains($visibleDownload->id, $visibleIds);
        $this->assertNotContains($crossScopeTeacherUpload->id, $visibleIds);
        $this->assertNotContains($wrongSubjectSemesterDownload->id, $visibleIds);
    }
}
