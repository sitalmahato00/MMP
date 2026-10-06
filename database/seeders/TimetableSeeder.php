<?php

namespace Database\Seeders;

use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\AcademicSession;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::where('is_active', true)->first();
        $program = Program::where('code', 'B.Tech')->first();

        if (!$session || !$program) {
            $this->command->warn('Missing session or program. Run AcademicSeeder and StudentSeeder first.');
            return;
        }

        $teacher = Teacher::first();
        $subjects = Subject::where('program_id', $program->id)
                           ->where('semester', 1)
                           ->get();

        if ($subjects->isEmpty() || !$teacher) {
            $this->command->warn('Missing subjects or teacher. Run SubjectSeeder and TeacherSeeder first.');
            return;
        }

        // Create timetable for Semester 1
        $timetable = Timetable::firstOrCreate(
            [
                'academic_session_id' => $session->id,
                'program_id'          => $program->id,
                'semester'            => 1,
            ],
            [
                'section'        => 'A',
                'effective_from' => now()->startOfMonth(),
                'is_active'      => true,
            ]
        );

        // Days and time slots
        $schedule = [
            ['day' => 'sunday',    'start' => '08:00', 'end' => '09:00'],
            ['day' => 'monday',    'start' => '08:00', 'end' => '09:00'],
            ['day' => 'tuesday',   'start' => '08:00', 'end' => '09:00'],
            ['day' => 'wednesday', 'start' => '08:00', 'end' => '09:00'],
            ['day' => 'thursday',  'start' => '08:00', 'end' => '09:00'],
        ];

        foreach ($subjects->take(5) as $index => $subject) {
            $slot = $schedule[$index] ?? $schedule[0];

            TimetableSlot::firstOrCreate(
                [
                    'timetable_id' => $timetable->id,
                    'subject_id'   => $subject->id,
                    'day_of_week'  => $slot['day'],
                ],
                [
                    'teacher_id'  => $teacher->id,
                    'start_time'  => $slot['start'],
                    'end_time'    => $slot['end'],
                    'room_number' => 'Room 10' . ($index + 1),
                    'type'        => $subject->type === 'practical' ? 'practical' : 'theory',
                    'duration'    => 1,
                ]
            );
        }

        $this->command->info('Timetable seeded successfully.');
    }
}
