<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get all exams
        $exams = Exam::all();

        foreach ($exams as $exam) {
            // Get subjects for this exam's department and programs
            $programIds = $exam->programs()->pluck('programs.id')->toArray();
            
            if (empty($programIds)) {
                continue;
            }

            // Get subjects for these programs
            $subjects = Subject::whereIn('program_id', $programIds)
                ->get();

            foreach ($subjects as $subject) {
                // Check if already exists
                $exists = DB::table('exam_subject_marking_schemes')
                    ->where('exam_id', $exam->id)
                    ->where('subject_id', $subject->id)
                    ->exists();

                if (!$exists) {
                    // Insert marking scheme
                    DB::table('exam_subject_marking_schemes')->insert([
                        'exam_id' => $exam->id,
                        'subject_id' => $subject->id,
                        'full_marks_internal_theory' => $subject->internal_theory_full_marks ?? 20,
                        'pass_marks_internal_theory' => $subject->internal_theory_pass_marks ?? 8,
                        'full_marks_external_theory' => $subject->external_theory_full_marks ?? 80,
                        'pass_marks_external_theory' => $subject->external_theory_pass_marks ?? 32,
                        'full_marks_internal_practical' => $subject->internal_practical_full_marks ?? 0,
                        'pass_marks_internal_practical' => $subject->internal_practical_pass_marks ?? 0,
                        'full_marks_external_practical' => $subject->external_practical_full_marks ?? 0,
                        'pass_marks_external_practical' => $subject->external_practical_pass_marks ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Exam subjects linked successfully!');
    }
}
