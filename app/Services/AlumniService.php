<?php

namespace App\Services;

use App\Models\{AcademicSession, Student};
use Illuminate\Support\Facades\DB;

/**
 * AlumniService — Handles the automated Student → Alumni conversion
 * triggered when an academic session ends.
 */
class AlumniService
{
    /**
     * Auto-convert all final-semester students to alumni when session ends.
     * This is the core automation engine triggered by session end.
     */
    public function convertFinalYearStudents(AcademicSession $session): array
    {
        $converted = 0;
        $failed = 0;
        $errors = [];

        // Get all final-semester active students in this session
        $finalStudents = Student::active()
            ->inSession($session->id)
            ->whereDoesntHave('alumnus')
            ->with(['user', 'department', 'program'])
            ->get()
            ->filter(fn ($student) => $student->isFinalSemester());

        DB::beginTransaction();
        try {
            foreach ($finalStudents as $student) {
                if (!$student->user) {
                    $failed++;
                    $errors[] = "Student {$student->id} is missing a linked user record.";
                    continue;
                }

                // Assign alumni role to user
                $student->user->syncRoles(['alumni']);

                // Create alumni record
                $alumnus = \App\Models\Alumni::withTrashed()->updateOrCreate([
                    'student_id' => $student->id,
                ], [
                    'user_id' => $student->user_id,
                    'department_id' => $student->department_id,
                    'program_id' => $student->program_id,
                    'graduation_year' => $session->end_date->format('Y'),
                    'is_featured' => false,
                    'is_verified' => true,
                ]);

                if (method_exists($alumnus, 'trashed') && $alumnus->trashed()) {
                    $alumnus->restore();
                }

                // Archive student record
                $student->update([
                    'status' => 'graduated',
                    'is_archived' => true,
                ]);

                // Log action
                \App\Models\AuditLog::log(
                    'alumni_auto_converted',
                    $student,
                    ['status' => 'active'],
                    ['status' => 'graduated']
                );

                $converted++;
            }

            DB::commit();

            // Bust public cache
            PublicDataService::invalidate('*');

        } catch (\Exception $e) {
            DB::rollBack();
            $failed++;
            $errors[] = $e->getMessage();
            \Log::error('AlumniService::convertFinalYearStudents failed', ['error' => $e->getMessage()]);
        }

        return compact('converted', 'failed', 'errors');
    }
}
