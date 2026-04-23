<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAttendanceTeacherIds extends Command
{
    protected $signature = 'attendance:fix-teacher-ids';
    protected $description = 'Fix attendance sessions with incorrect teacher_id values';

    public function handle()
    {
        $this->info('Fixing attendance session teacher IDs...');
        
        // Get current academic session
        $currentSession = DB::table('academic_sessions')
            ->where('is_active', true)
            ->first();
        
        if (!$currentSession) {
            $this->error('No current academic session found!');
            return 1;
        }
        
        $this->info("Current session: {$currentSession->name}");
        
        // Get all attendance sessions
        $sessions = DB::table('attendance_sessions')
            ->where('academic_session_id', $currentSession->id)
            ->get();
        
        $fixed = 0;
        $alreadyCorrect = 0;
        $noTeacher = 0;
        
        foreach ($sessions as $session) {
            // Check if current teacher_id is valid
            $isValid = DB::table('subject_teacher')
                ->where('subject_id', $session->subject_id)
                ->where('teacher_id', $session->teacher_id)
                ->where('academic_session_id', $session->academic_session_id)
                ->exists();
            
            if ($isValid) {
                $alreadyCorrect++;
                continue;
            }
            
            // Find a valid teacher for this subject
            $validTeacher = DB::table('subject_teacher')
                ->where('subject_id', $session->subject_id)
                ->where('academic_session_id', $session->academic_session_id)
                ->first();
            
            if ($validTeacher) {
                DB::table('attendance_sessions')
                    ->where('id', $session->id)
                    ->update(['teacher_id' => $validTeacher->teacher_id]);
                
                $this->line("✓ Fixed session #{$session->id} - Updated teacher_id to {$validTeacher->teacher_id}");
                $fixed++;
            } else {
                $this->warn("✗ Session #{$session->id} has no valid teacher for subject #{$session->subject_id}");
                $noTeacher++;
            }
        }
        
        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Already Correct', $alreadyCorrect],
                ['Fixed', $fixed],
                ['No Valid Teacher', $noTeacher],
            ]
        );
        
        if ($noTeacher > 0) {
            $this->newLine();
            $this->warn("There are {$noTeacher} sessions with no valid teacher.");
            $this->warn('These sessions should be deleted or assigned to a valid teacher manually.');
        }
        
        $this->newLine();
        $this->info('Done! Please refresh your browser.');
        
        return 0;
    }
}
