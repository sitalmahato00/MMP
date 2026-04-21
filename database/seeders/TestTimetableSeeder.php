<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use Illuminate\Database\Seeder;

class TestTimetableSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Creating Test Timetable with Group Structure...');

        // Get IT Department
        $department = Department::where('code', 'IT')->first();
        
        if (!$department) {
            $this->command->error('❌ IT Department not found!');
            return;
        }

        $session = AcademicSession::where('is_active', true)->first();
        $program = $department->programs()->first();
        $teachers = $department->teachers()->where('is_active', true)->take(3)->get();
        $subjects = $program->subjects()->where('semester', 1)->get();

        if (!$session || !$program || $teachers->isEmpty() || $subjects->count() < 2) {
            $this->command->error('❌ Missing required data (session, program, teachers, or subjects)!');
            $this->command->error("Found: Teachers: {$teachers->count()}, Subjects: {$subjects->count()}");
            return;
        }

        // Use available subjects (cycle through if needed)
        $subjectIds = $subjects->pluck('id')->toArray();

        // Create a clean test timetable
        $timetable = Timetable::updateOrCreate(
            [
                'academic_session_id' => $session->id,
                'program_id' => $program->id,
                'semester' => 1,
                'section' => 'TEST',
            ],
            [
                'effective_from' => now()->startOfWeek(),
                'is_active' => true,
            ]
        );

        // Clear existing slots
        $timetable->slots()->delete();

        // Create test slots with different group configurations
        $testSlots = [
            // Monday - Common class (no group)
            [
                'day_of_week' => 'monday',
                'start_time' => '06:30',
                'end_time' => '07:15',
                'subject_id' => $subjectIds[0],
                'teacher_id' => $teachers[0]->id,
                'room_number' => 'Room 101',
                'type' => 'theory',
                'group' => '', // Common to all groups
                'duration' => 1,
            ],
            // Monday - Group A specific
            [
                'day_of_week' => 'monday',
                'start_time' => '07:15',
                'end_time' => '08:00',
                'subject_id' => $subjectIds[1 % count($subjectIds)],
                'teacher_id' => $teachers[1 % $teachers->count()]->id,
                'room_number' => 'Lab A',
                'type' => 'lab',
                'group' => 'A',
                'duration' => 1,
            ],
            // Monday - Group B specific
            [
                'day_of_week' => 'monday',
                'start_time' => '07:15',
                'end_time' => '08:00',
                'subject_id' => $subjectIds[0],
                'teacher_id' => $teachers[2 % $teachers->count()]->id,
                'room_number' => 'Lab B',
                'type' => 'lab',
                'group' => 'B',
                'duration' => 1,
            ],
            // Tuesday - Another common class
            [
                'day_of_week' => 'tuesday',
                'start_time' => '06:30',
                'end_time' => '07:15',
                'subject_id' => $subjectIds[1 % count($subjectIds)],
                'teacher_id' => $teachers[0]->id,
                'room_number' => 'Room 102',
                'type' => 'theory',
                'group' => '',
                'duration' => 1,
            ],
        ];

        foreach ($testSlots as $slotData) {
            TimetableSlot::create(array_merge(['timetable_id' => $timetable->id], $slotData));
        }

        $this->command->info("✅ Created test timetable with " . count($testSlots) . " slots");
        $this->command->info("📋 Timetable ID: {$timetable->id}");
        $this->command->info("🔗 Edit URL: /hod/timetable/{$timetable->id}/edit");
    }
}