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

class ComprehensiveTimetableSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting Comprehensive Timetable Seeding...');

        // Get current academic session
        $session = AcademicSession::where('is_active', true)->first();
        
        if (!$session) {
            $this->command->error('❌ No active academic session found!');
            return;
        }

        // Get all departments with programs
        $departments = Department::with(['programs.subjects', 'teachers'])->get();

        foreach ($departments as $department) {
            $this->seedDepartmentTimetables($department, $session);
        }

        $this->command->info('✅ Comprehensive timetable seeding completed successfully!');
    }

    private function seedDepartmentTimetables(Department $department, AcademicSession $session): void
    {
        $this->command->info("📚 Seeding timetables for {$department->name} Department...");

        $teachers = $department->teachers()->where('is_active', true)->get();
        
        if ($teachers->isEmpty()) {
            $this->command->warn("⚠️  No teachers found for {$department->name} department");
            return;
        }

        foreach ($department->programs as $program) {
            $this->seedProgramTimetables($program, $session, $teachers, $department);
        }
    }

    private function seedProgramTimetables(Program $program, AcademicSession $session, $teachers, Department $department): void
    {
        // Create timetables for semesters 1-8
        for ($semester = 1; $semester <= 8; $semester++) {
            $subjects = $program->subjects()
                ->where('semester', $semester)
                ->where('is_active', true)
                ->get();

            if ($subjects->isEmpty()) {
                continue;
            }

            // Create timetables for different sections/groups
            $sections = ['A', 'B'];
            
            foreach ($sections as $section) {
                $this->createTimetableForSection($program, $semester, $section, $session, $subjects, $teachers, $department);
            }
        }
    }

    private function createTimetableForSection(Program $program, int $semester, string $section, AcademicSession $session, $subjects, $teachers, Department $department): void
    {
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
                'is_active' => $semester <= 2, // Only first 2 semesters active by default
            ]
        );

        // Clear existing slots
        $timetable->slots()->delete();

        // Define realistic time periods matching your format
        $timePeriods = [
            ['start' => '06:30', 'end' => '07:15'],
            ['start' => '07:15', 'end' => '08:00'],
            ['start' => '08:00', 'end' => '08:45'],
            ['start' => '08:45', 'end' => '09:30'],
            ['start' => '09:30', 'end' => '10:15'],
            ['start' => '10:15', 'end' => '11:00'],
            ['start' => '11:00', 'end' => '11:45'],
            ['start' => '11:45', 'end' => '12:30'],
            ['start' => '12:30', 'end' => '13:15'],
        ];

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        $subjectsArray = $subjects->values()->all();
        $teacherIndex = 0;
        $slotCount = 0;

        // Create realistic schedule
        foreach ($days as $dayIndex => $day) {
            $dailySubjectIndex = 0;
            
            foreach ($timePeriods as $periodIndex => $period) {
                // Skip some periods for breaks and lunch
                if ($this->shouldSkipPeriod($dayIndex, $periodIndex)) {
                    continue;
                }

                // Get subject for this slot
                $subject = $this->getSubjectForSlot($subjectsArray, $dailySubjectIndex, $dayIndex, $periodIndex);
                
                if (!$subject) {
                    continue;
                }

                // Get appropriate teacher
                $teacher = $this->getTeacherForSubject($subject, $teachers, $teacherIndex);
                
                // Determine slot properties
                $slotData = $this->getSlotProperties($subject, $teacher, $section, $department, $slotCount);

                // Create the slot
                TimetableSlot::create([
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $day,
                    'start_time' => $period['start'],
                    'end_time' => $period['end'],
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'room_number' => $slotData['room'],
                    'type' => $slotData['type'],
                    'group' => $slotData['group'],
                    'duration' => $slotData['duration'],
                ]);

                $dailySubjectIndex++;
                $teacherIndex++;
                $slotCount++;

                // Handle joint classes (2-period duration)
                if ($slotData['duration'] > 1 && $periodIndex < count($timePeriods) - 1) {
                    // Skip next period for joint class
                    continue;
                }
            }
        }

        $this->command->info("   ✓ Created timetable for {$program->name} - Semester {$semester}, Section {$section} ({$slotCount} slots)");
    }

    private function shouldSkipPeriod(int $dayIndex, int $periodIndex): bool
    {
        // Skip some periods for realistic breaks
        if ($periodIndex === 4 && $dayIndex < 3) { // Morning break on some days
            return true;
        }
        
        if ($periodIndex === 7 && $dayIndex >= 3) { // Lunch break on some days
            return true;
        }

        // Saturday has fewer periods
        if ($dayIndex === 5 && $periodIndex > 5) {
            return true;
        }

        return false;
    }

    private function getSubjectForSlot(array $subjects, int $index, int $dayIndex, int $periodIndex): ?Subject
    {
        if (empty($subjects)) {
            return null;
        }

        // Rotate through subjects with some logic
        $subjectIndex = ($index + $dayIndex * 2 + $periodIndex) % count($subjects);
        return $subjects[$subjectIndex];
    }

    private function getTeacherForSubject(Subject $subject, $teachers, int $index): Teacher
    {
        // Try to get subject-specific teacher first
        $subjectTeachers = $subject->teachers()->where('is_active', true)->get();
        
        if ($subjectTeachers->isNotEmpty()) {
            return $subjectTeachers->random();
        }

        // Fallback to department teachers
        return $teachers[$index % $teachers->count()];
    }

    private function getSlotProperties(Subject $subject, Teacher $teacher, string $section, Department $department, int $slotCount): array
    {
        // Determine type based on subject
        $type = 'theory';
        if (str_contains(strtolower($subject->name), 'lab') || str_contains(strtolower($subject->name), 'practical')) {
            $type = 'lab';
        } elseif (str_contains(strtolower($subject->name), 'workshop') || str_contains(strtolower($subject->name), 'studio')) {
            $type = 'practical';
        }

        // Determine room based on type and department
        $room = $this->getRoomForSlot($type, $department, $slotCount);

        // Determine group (some subjects have group divisions)
        $group = '';
        if ($type === 'lab' || $type === 'practical') {
            $group = ['A', 'B', 'C'][rand(0, 2)]; // Random group for lab subjects
        }

        // Determine duration (some subjects are joint classes)
        $duration = 1;
        if ($type === 'lab' || str_contains(strtolower($subject->name), 'project')) {
            $duration = rand(1, 2); // Lab subjects can be 1-2 periods
        }

        return [
            'type' => $type,
            'room' => $room,
            'group' => $group,
            'duration' => $duration,
        ];
    }

    private function getRoomForSlot(string $type, Department $department, int $slotCount): string
    {
        $deptCode = $department->code ?? 'GEN';
        
        switch ($type) {
            case 'lab':
                $labNumbers = ['Lab-1', 'Lab-2', 'Computer Lab', 'Physics Lab', 'Chemistry Lab'];
                return $deptCode . ' ' . $labNumbers[$slotCount % count($labNumbers)];
                
            case 'practical':
                $studioNumbers = ['Studio-A', 'Studio-B', 'Workshop-1', 'Workshop-2'];
                return $deptCode . ' ' . $studioNumbers[$slotCount % count($studioNumbers)];
                
            default:
                $roomNumber = 201 + ($slotCount % 20);
                return "Room {$roomNumber}";
        }
    }
}