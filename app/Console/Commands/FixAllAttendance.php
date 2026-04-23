<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAllAttendance extends Command
{
    protected $signature = 'attendance:fix-all';
    protected $description = 'Fix ALL attendance sessions by updating teacher_id to match subject assignments';

    public function handle()
    {
        $this->info('Fixing ALL attendance sessions...');
        
        // Update all attendance sessions to use the correct teacher from subject_teacher
        $affectedRows = DB::update("
            UPDATE attendance_sessions a
            JOIN subject_teacher st ON a.subject_id = st.subject_id 
                AND a.academic_session_id = st.academic_session_id
            SET a.teacher_id = st.teacher_id
            WHERE a.teacher_id != st.teacher_id
        ");
        
        if ($affectedRows > 0) {
            $this->info("✓ Fixed {$affectedRows} attendance sessions!");
        } else {
            $this->info('✓ No sessions needed fixing.');
        }
        
        // Check for orphaned sessions (no teacher assigned to subject)
        $orphaned = DB::select("
            SELECT COUNT(*) as count
            FROM attendance_sessions a
            WHERE NOT EXISTS (
                SELECT 1 
                FROM subject_teacher st 
                WHERE st.subject_id = a.subject_id 
                AND st.academic_session_id = a.academic_session_id
            )
        ");
        
        if ($orphaned[0]->count > 0) {
            $this->warn("⚠ Found {$orphaned[0]->count} orphaned sessions (no teacher assigned to subject).");
            $this->warn('These sessions should be deleted or a teacher should be assigned to the subject.');
        }
        
        $this->newLine();
        $this->info('Done! Please refresh your browser and try again.');
        
        return 0;
    }
}
