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

class ArchitectureTimetableSeeder extends Seeder
{
    public function run(): void
    {
        // Get Architecture department
        $department = Department::where('code', 'AR')->first();
        
        if (!$department) {
            $this->command->error('Architecture department not found!');
            return;
        }

        // Get current academic session
        $session = AcademicSession::where('is_active', true)->first();
        
        if (!$session) {
            $this->command->error('No active academic session found!');
            return;
        }

        // Get Architecture program
        $program = $department->programs()->first();
        
        if (!$program) {
            $this->command->error('No program found for Architecture department!');
            return;
        }

        // Get teachers from Architecture department
        $teachers = $department->teachers()->where('is_active', true)->get();
        
        if ($teachers->isEmpty()) {
            $this->command->error('No teachers found for Architecture department!');
            return;
        }

        $primaryTeacher = $teachers->first();

        // Create timetables for semesters 1-6
        for ($semester = 1; $semester <= 6; $semester++) {
            // Get subjects for this semester
            $subjects = $program->subjects()
                ->where('semester', $semester)
                ->where('is_active', true)
                ->get();

            if ($subjects->isEmpty()) {
                $this->command->warn("No subjects found for semester {$semester}");
                continue;
            }

            // Create timetable
            $timetable = Timetable::updateOrCreate(
                [
                    'academic_session_id' => $session->id,
                    'program_id' => $program->id,
                    'semester' => $semester,
                    'section' => 'A',
                ],
                [
                    'effective_from' => now()->startOfWeek(),
                    'is_active' => $semester === 1, // Only semester 1 is active by default
                ]
            );

            // Define time slots
            $timeSlots = [
                ['start' => '07:00:00', 'end' => '07:45:00'],
                ['start' => '07:45:00', 'end' => '08:30:00'],
                ['start' => '08:30:00', 'end' => '09:15:00'],
                ['start' => '09:15:00', 'end' => '10:00:00'],
                ['start' => '10:15:00', 'end' => '11:00:00'], // After break
                ['start' => '11:00:00', 'end' => '11:45:00'],
                ['start' => '11:45:00', 'end' => '12:30:00'],
            ];

            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            
            $slotIndex = 0;
            $subjectIndex = 0;
            $subjectsArray = $subjects->values()->all();

            // Create slots for each day
            foreach ($days as $day) {
                foreach ($timeSlots as $timeSlot) {
                    if ($subjectIndex >= count($subjectsArray)) {
                        $subjectIndex = 0; // Loop back to first subject
                    }

                    $subject = $subjectsArray[$subjectIndex];
                    
                    // Assign teacher (rotate through available teachers)
                    $teacher = $teachers[$slotIndex % $teachers->count()];

                    // Determine room and type based on subject type
                    $type = $subject->type === 'practical' || $subject->type === 'both' ? 'lab' : 'theory';
                    $room = $type === 'lab' ? 'Architecture Studio ' . (($slotIndex % 3) + 1) : 'Room ' . (201 + ($slotIndex % 5));

                    TimetableSlot::updateOrCreate(
                        [
                            'timetable_id' => $timetable->id,
                            'day_of_week' => $day,
                            'start_time' => $timeSlot['start'],
                            'subject_id' => $subject->id,
                        ],
                        [
                            'end_time' => $timeSlot['end'],
                            'teacher_id' => $teacher->id,
                            'room_number' => $room,
                            'type' => $type,
                            'group' => 'A',
                        ]
                    );

                    $slotIndex++;
                    $subjectIndex++;
                }
            }

            $this->command->info("Created timetable for {$program->name} - Semester {$semester} with " . ($slotIndex) . " slots");
        }

        $this->command->info('Architecture timetables seeded successfully!');
    }
}
