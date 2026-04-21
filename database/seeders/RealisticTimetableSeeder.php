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

class RealisticTimetableSeeder extends Seeder
{
    private array $timeSlots = [
        ['start' => '06:30', 'end' => '07:15'],
        ['start' => '07:15', 'end' => '08:00'],
        ['start' => '08:00', 'end' => '08:45'],
        ['start' => '08:45', 'end' => '09:30'],
        ['start' => '09:30', 'end' => '10:15'], // Break after this
        ['start' => '10:15', 'end' => '11:00'],
        ['start' => '11:00', 'end' => '11:45'],
        ['start' => '11:45', 'end' => '12:30'],
        ['start' => '12:30', 'end' => '13:15'], // Lunch break after this
    ];

    private array $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    public function run(): void
    {
        $this->command->info('🎯 Creating Realistic Class Timetables...');

        // Get IT Department for demonstration
        $department = Department::where('code', 'IT')->first();
        
        if (!$department) {
            $this->command->error('❌ IT Department not found!');
            return;
        }

        $session = AcademicSession::where('is_active', true)->first();
        
        if (!$session) {
            $this->command->error('❌ No active academic session found!');
            return;
        }

        $program = $department->programs()->first();
        $teachers = $department->teachers()->where('is_active', true)->get();

        if (!$program || $teachers->isEmpty()) {
            $this->command->error('❌ No program or teachers found for IT department!');
            return;
        }

        // Create realistic timetable for Semester 1, Section A
        $this->createRealisticTimetable($program, 1, 'A', $session, $teachers);
        
        // Create timetable with groups for Semester 2, Section A
        $this->createGroupBasedTimetable($program, 2, 'A', $session, $teachers);

        $this->command->info('✅ Realistic timetables created successfully!');
    }

    private function createRealisticTimetable(Program $program, int $semester, string $section, AcademicSession $session, $teachers): void
    {
        $this->command->info("📅 Creating realistic timetable for {$program->name} - Semester {$semester}, Section {$section}");

        // Get subjects for this semester
        $subjects = $program->subjects()
            ->where('semester', $semester)
            ->where('is_active', true)
            ->get();

        if ($subjects->isEmpty()) {
            $this->command->warn("⚠️  No subjects found for semester {$semester}");
            return;
        }

        // Create timetable
        $timetable = Timetable::updateOrCreate(
            [
                'academic_session_id' => $session->id,
                'program_id' => $program->id,
                'semester' => $semester,
                'section' => $section,
            ],
            [
                'effective_from' => now()->startOfWeek(),
                'is_active' => true,
            ]
        );

        // Clear existing slots
        $timetable->slots()->delete();

        // Create realistic weekly schedule
        $schedule = $this->generateRealisticSchedule($subjects, $teachers);

        $slotCount = 0;
        foreach ($this->days as $dayIndex => $day) {
            if (!isset($schedule[$day])) continue;

            foreach ($schedule[$day] as $periodIndex => $slotData) {
                if (!$slotData) continue;

                TimetableSlot::create([
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $day,
                    'start_time' => $this->timeSlots[$periodIndex]['start'],
                    'end_time' => $this->timeSlots[$periodIndex]['end'],
                    'subject_id' => $slotData['subject_id'],
                    'teacher_id' => $slotData['teacher_id'],
                    'room_number' => $slotData['room'],
                    'type' => $slotData['type'],
                    'group' => $slotData['group'] ?? '',
                    'duration' => $slotData['duration'] ?? 1,
                ]);

                $slotCount++;
            }
        }

        $this->command->info("   ✓ Created {$slotCount} realistic time slots");
    }

    private function createGroupBasedTimetable(Program $program, int $semester, string $section, AcademicSession $session, $teachers): void
    {
        $this->command->info("👥 Creating group-based timetable for {$program->name} - Semester {$semester}, Section {$section}");

        $subjects = $program->subjects()
            ->where('semester', $semester)
            ->where('is_active', true)
            ->get();

        if ($subjects->isEmpty()) {
            return;
        }

        // Create timetable
        $timetable = Timetable::updateOrCreate(
            [
                'academic_session_id' => $session->id,
                'program_id' => $program->id,
                'semester' => $semester,
                'section' => $section,
            ],
            [
                'effective_from' => now()->startOfWeek(),
                'is_active' => true,
            ]
        );

        // Clear existing slots
        $timetable->slots()->delete();

        // Create group-based schedule
        $groups = ['A', 'B', 'C'];
        $slotCount = 0;

        foreach ($this->days as $day) {
            foreach ($this->timeSlots as $periodIndex => $timeSlot) {
                // Skip break periods
                if ($periodIndex === 4 || $periodIndex === 8) continue;

                $subject = $subjects->random();
                
                // Determine if this should be a group-based class
                $isGroupBased = $this->shouldBeGroupBased($subject, $periodIndex);

                if ($isGroupBased) {
                    // Create separate slots for each group
                    foreach ($groups as $group) {
                        $teacher = $teachers->random();
                        $slotData = $this->getSlotData($subject, $teacher, $group);

                        TimetableSlot::create([
                            'timetable_id' => $timetable->id,
                            'day_of_week' => $day,
                            'start_time' => $timeSlot['start'],
                            'end_time' => $timeSlot['end'],
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'room_number' => $slotData['room'] . '-' . $group,
                            'type' => $slotData['type'],
                            'group' => $group,
                            'duration' => $slotData['duration'],
                        ]);

                        $slotCount++;
                    }
                } else {
                    // Create single slot for all groups
                    $teacher = $teachers->random();
                    $slotData = $this->getSlotData($subject, $teacher);

                    TimetableSlot::create([
                        'timetable_id' => $timetable->id,
                        'day_of_week' => $day,
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'room_number' => $slotData['room'],
                        'type' => $slotData['type'],
                        'group' => '',
                        'duration' => $slotData['duration'],
                    ]);

                    $slotCount++;
                }
            }
        }

        $this->command->info("   ✓ Created {$slotCount} group-based time slots");
    }

    private function generateRealisticSchedule($subjects, $teachers): array
    {
        $schedule = [];
        
        // Define subject priorities and frequencies
        $coreSubjects = $subjects->filter(fn($s) => str_contains(strtolower($s->name), 'programming') || str_contains(strtolower($s->name), 'mathematics'));
        $labSubjects = $subjects->filter(fn($s) => str_contains(strtolower($s->name), 'lab') || str_contains(strtolower($s->name), 'practical'));
        $theorySubjects = $subjects->filter(fn($s) => !str_contains(strtolower($s->name), 'lab') && !str_contains(strtolower($s->name), 'practical'));

        foreach ($this->days as $dayIndex => $day) {
            $schedule[$day] = [];
            
            for ($period = 0; $period < count($this->timeSlots); $period++) {
                // Skip break periods
                if ($period === 4 || $period === 8) {
                    $schedule[$day][$period] = null;
                    continue;
                }

                // Saturday has fewer periods
                if ($dayIndex === 5 && $period > 5) {
                    $schedule[$day][$period] = null;
                    continue;
                }

                // Choose subject type based on time and day
                $subject = $this->chooseSubjectForSlot($period, $dayIndex, $coreSubjects, $labSubjects, $theorySubjects);
                $teacher = $teachers->random();

                if ($subject) {
                    $schedule[$day][$period] = $this->getSlotData($subject, $teacher);
                } else {
                    $schedule[$day][$period] = null;
                }
            }
        }

        return $schedule;
    }

    private function chooseSubjectForSlot(int $period, int $dayIndex, $coreSubjects, $labSubjects, $theorySubjects): ?Subject
    {
        // Morning periods (0-3) - prefer core subjects
        if ($period <= 3 && $coreSubjects->isNotEmpty()) {
            return $coreSubjects->random();
        }

        // Mid-morning (5-6) - prefer lab subjects
        if ($period >= 5 && $period <= 6 && $labSubjects->isNotEmpty()) {
            return $labSubjects->random();
        }

        // Afternoon - theory subjects
        if ($theorySubjects->isNotEmpty()) {
            return $theorySubjects->random();
        }

        // Fallback to any available subject
        $allSubjects = $coreSubjects->merge($labSubjects)->merge($theorySubjects);
        return $allSubjects->isNotEmpty() ? $allSubjects->random() : null;
    }

    private function shouldBeGroupBased(Subject $subject, int $periodIndex): bool
    {
        // Lab subjects are usually group-based
        if (str_contains(strtolower($subject->name), 'lab') || str_contains(strtolower($subject->name), 'practical')) {
            return true;
        }

        // Some theory subjects in certain periods
        if ($periodIndex >= 5 && rand(0, 100) < 30) { // 30% chance for afternoon periods
            return true;
        }

        return false;
    }

    private function getSlotData(Subject $subject, Teacher $teacher, ?string $group = null): array
    {
        $subjectName = strtolower($subject->name);
        
        // Determine type
        $type = 'theory';
        if (str_contains($subjectName, 'lab')) {
            $type = 'lab';
        } elseif (str_contains($subjectName, 'practical') || str_contains($subjectName, 'workshop')) {
            $type = 'practical';
        }

        // Determine room
        $room = match($type) {
            'lab' => 'IT Lab ' . rand(1, 3),
            'practical' => 'Workshop ' . rand(1, 2),
            default => 'Room ' . (201 + rand(0, 15))
        };

        // Determine duration
        $duration = 1;
        if ($type === 'lab' || str_contains($subjectName, 'project')) {
            $duration = rand(1, 2);
        }

        return [
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'room' => $room,
            'type' => $type,
            'duration' => $duration,
            'group' => $group,
        ];
    }
}