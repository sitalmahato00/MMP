<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Support\Str;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Database\Seeder;

class HodTimetableSeeder extends Seeder
{
    public function run(): void
    {
        // Use or create a department
        $department = Department::firstOrCreate(
            ['name' => 'Test Department'],
            ['code' => 'TD', 'is_active' => true]
        );

        // Create a HOD user and assign to department
        $hodUser = User::firstOrCreate(
            ['email' => 'hod-test@example.com'],
            ['name' => 'Test HOD', 'password' => bcrypt('password'), 'is_active' => true]
        );
        try {
            $hodUser->assignRole('hod');
        } catch (\Throwable $e) {
            // role may not exist in some test setups
        }
        $department->hod_id = $hodUser->id;
        $department->save();

        // Create a program under the department
        $program = Program::firstOrCreate(
            ['department_id' => $department->id, 'name' => 'Test Program'],
            ['code' => 'TP', 'slug' => Str::slug('Test Program'), 'total_semesters' => 8, 'is_active' => true]
        );

        // Ensure there's an active academic session
        $session = AcademicSession::where('is_active', true)->first();
        if (!$session) {
            $session = AcademicSession::create([
                'name' => '2081-2082',
                'name_bs' => '2081-2082',
                'start_date' => now()->subMonths(1)->toDateString(),
                'end_date' => now()->addMonths(10)->toDateString(),
                'is_active' => true,
                'status' => 'active',
            ]);
        }

        // Create a subject for semester 1
        $subject = Subject::firstOrCreate(
            ['program_id' => $program->id, 'semester' => 1, 'name' => 'C Programming'],
            ['code' => 'C101']
        );

        // Create a user and teacher
        $user = User::firstOrCreate(
            ['email' => 'hod-test-teacher@example.com'],
            ['name' => 'Test Teacher', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $user->assignRole('teacher');

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['department_id' => $department->id, 'designation' => 'Lecturer', 'is_active' => true]
        );

        // Attach subject to teacher for current session (if pivot table exists)
        try {
            $teacher->subjects()->syncWithoutDetaching([$subject->id => ['academic_session_id' => $session->id, 'role' => 'teacher']]);
        } catch (\Throwable $e) {
            // ignore if pivot table or columns missing in test env
        }

        // Create a timetable for semester 1
        $timetable = Timetable::firstOrCreate([
            'program_id' => $program->id,
            'semester' => 1,
        ], [
            'academic_session_id' => $session->id,
            'section' => null,
            'effective_from' => now()->toDateString(),
            'is_active' => true,
        ]);

        // Create two sample slots (Group A and Group B) for Monday
        TimetableSlot::updateOrCreate([
            'timetable_id' => $timetable->id,
            'day_of_week' => 'monday',
            'start_time' => '06:30',
            'end_time' => '08:15',
        ], [
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'room_number' => '101',
            'type' => 'theory',
            'group' => 'A',
            'duration' => 2,
        ]);

        TimetableSlot::updateOrCreate([
            'timetable_id' => $timetable->id,
            'day_of_week' => 'monday',
            'start_time' => '08:30',
            'end_time' => '10:15',
        ], [
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'room_number' => '102',
            'type' => 'theory',
            'group' => 'B',
            'duration' => 2,
        ]);

        $this->command->info('HOD test timetable seeded.');
        $this->command->info('Teacher login: hod-test-teacher@example.com / password');
    }
}
