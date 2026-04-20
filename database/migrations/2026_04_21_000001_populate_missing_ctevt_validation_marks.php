<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Populate missing CTEVT validation marks for existing mark records
        $this->populateMissingCTEVTValidationMarks();
    }

    public function down(): void
    {
        // This migration is data-only, no rollback needed
    }

    private function populateMissingCTEVTValidationMarks(): void
    {
        // Get all marks that don't have CTEVT validation marks populated
        $marksWithoutValidation = DB::table('marks')
            ->whereNull('ctevt_pass_marks_internal_theory')
            ->orWhereNull('ctevt_pass_marks_external_theory')
            ->get();

        foreach ($marksWithoutValidation as $mark) {
            // Try to get validation marks from exam-specific marking scheme first
            $scheme = DB::table('exam_subject_marking_schemes')
                ->where('exam_id', $mark->exam_id)
                ->where('subject_id', $mark->subject_id)
                ->first();

            if ($scheme) {
                // Use exam-specific marking scheme
                DB::table('marks')
                    ->where('id', $mark->id)
                    ->update([
                        'ctevt_full_marks_internal_theory' => $scheme->full_marks_internal_theory,
                        'ctevt_pass_marks_internal_theory' => $scheme->pass_marks_internal_theory,
                        'ctevt_full_marks_external_theory' => $scheme->full_marks_external_theory,
                        'ctevt_pass_marks_external_theory' => $scheme->pass_marks_external_theory,
                        'ctevt_full_marks_internal_practical' => $scheme->full_marks_internal_practical,
                        'ctevt_pass_marks_internal_practical' => $scheme->pass_marks_internal_practical,
                        'ctevt_full_marks_external_practical' => $scheme->full_marks_external_practical,
                        'ctevt_pass_marks_external_practical' => $scheme->pass_marks_external_practical,
                        'updated_at' => now(),
                    ]);
            } else {
                // Fallback to subject defaults
                $subject = DB::table('subjects')
                    ->where('id', $mark->subject_id)
                    ->first();

                if ($subject) {
                    DB::table('marks')
                        ->where('id', $mark->id)
                        ->update([
                            'ctevt_full_marks_internal_theory' => $subject->full_marks_internal_theory ?? 0,
                            'ctevt_pass_marks_internal_theory' => $subject->pass_marks_internal_theory ?? 0,
                            'ctevt_full_marks_external_theory' => $subject->full_marks_external_theory ?? 0,
                            'ctevt_pass_marks_external_theory' => $subject->pass_marks_external_theory ?? 0,
                            'ctevt_full_marks_internal_practical' => $subject->full_marks_internal_practical ?? 0,
                            'ctevt_pass_marks_internal_practical' => $subject->pass_marks_internal_practical ?? 0,
                            'ctevt_full_marks_external_practical' => $subject->full_marks_external_practical ?? 0,
                            'ctevt_pass_marks_external_practical' => $subject->pass_marks_external_practical ?? 0,
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        echo "Populated CTEVT validation marks for " . $marksWithoutValidation->count() . " mark records.\n";
    }
};