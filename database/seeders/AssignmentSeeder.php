<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Program;
use App\Models\AcademicSession;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::current();
        
        if (!$session) {
            $this->command->warn('No active academic session found. Skipping assignment seeding.');
            return;
        }

        // Get all teachers with their subjects
        $teachers = Teacher::with(['subjects' => function($q) use ($session) {
            $q->wherePivot('academic_session_id', $session->id);
        }])->get();

        $assignmentTitles = [
            'Lab Report - Experiment 1',
            'Project Proposal Submission',
            'Mid-term Assignment',
            'Case Study Analysis',
            'Research Paper Draft',
            'Programming Assignment 1',
            'Design Project',
            'Technical Documentation',
            'Group Presentation',
            'Final Project Report',
        ];

        $descriptions = [
            'Complete the lab report based on the experiment conducted in class. Include methodology, observations, and conclusions.',
            'Submit a detailed project proposal including objectives, methodology, timeline, and expected outcomes.',
            'Answer all questions from chapters 1-5. Show all working and provide detailed explanations.',
            'Analyze the given case study and provide recommendations based on theoretical concepts.',
            'Submit the first draft of your research paper. Include introduction, literature review, and methodology.',
            'Implement the given algorithm and submit the source code with documentation.',
            'Design a solution for the given problem statement. Include diagrams and specifications.',
            'Prepare comprehensive technical documentation for the assigned system.',
            'Prepare and deliver a group presentation on the assigned topic.',
            'Submit the final project report with all deliverables and documentation.',
        ];

        $assignmentCount = 0;

        foreach ($teachers as $teacher) {
            foreach ($teacher->subjects as $subject) {
                // Create 2-3 assignments per subject
                $numAssignments = rand(2, 3);
                
                for ($i = 0; $i < $numAssignments; $i++) {
                    $daysOffset = rand(-30, 30); // Some past, some future
                    $dueDate = Carbon::now()->addDays($daysOffset);
                    
                    $assignment = Assignment::create([
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'program_id' => $subject->program_id,
                        'semester' => $subject->semester,
                        'section' => $subject->pivot->section ?? null,
                        'title' => $assignmentTitles[array_rand($assignmentTitles)],
                        'description' => $descriptions[array_rand($descriptions)],
                        'due_date' => $dueDate,
                    ]);

                    $assignmentCount++;

                    // Get students for this program and semester
                    $students = Student::where('program_id', $subject->program_id)
                        ->where('current_semester', $subject->semester)
                        ->where('status', 'active')
                        ->get();

                    // Create submissions for some students (60-80% submission rate)
                    foreach ($students as $student) {
                        if (rand(1, 100) <= 70) { // 70% submission rate
                            $submittedAt = Carbon::parse($assignment->due_date)->subDays(rand(1, 5));
                            $isLate = $submittedAt->isAfter($assignment->due_date);
                            
                            AssignmentSubmission::create([
                                'assignment_id' => $assignment->id,
                                'student_id' => $student->id,
                                'student_note' => 'Assignment submitted successfully.',
                                'status' => $isLate ? 'late' : (rand(1, 100) <= 60 ? 'graded' : 'submitted'),
                                'marks_obtained' => rand(1, 100) <= 60 ? rand(50, 100) : null,
                                'teacher_feedback' => rand(1, 100) <= 60 ? 'Good work!' : null,
                                'created_at' => $submittedAt,
                                'updated_at' => rand(1, 100) <= 60 ? $submittedAt->addDays(rand(1, 3)) : $submittedAt,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info("Created {$assignmentCount} assignments with submissions.");
    }
}
