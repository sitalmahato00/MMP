<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReassignAttendanceToBinay extends Command
{
    protected $signature = 'attendance:reassign-to-binay';
    protected $description = 'Reassign all Computer Graphics attendance sessions to binay';

    public function handle()
    {
        // Get binay's teacher ID
        $binay = DB::table('users')
            ->join('teachers', 'users.id', '=', 'teachers.user_id')
            ->where('users.name', 'binay')
            ->select('teachers.id as teacher_id', 'users.name')
            ->first();
        
        if (!$binay) {
            $this->error('Teacher "binay" not found!');
            return 1;
        }
        
        $this->info("Found teacher: {$binay->name} (ID: {$binay->teacher_id})");
        
        // Get Computer Graphics subject
        $subject = DB::table('subjects')
            ->where('name', 'Computer Graphics')
            ->first();
        
        if (!$subject) {
            $this->error('Subject "Computer Graphics" not found!');
            return 1;
        }
        
        $this->info("Found subject: {$subject->name} (ID: {$subject->id})");
        $this->newLine();
        
        // Update all Computer Graphics attendance sessions to binay
        $updated = DB::table('attendance_sessions')
            ->where('subject_id', $subject->id)
            ->update(['teacher_id' => $binay->teacher_id]);
        
        $this->info("✓ Updated {$updated} attendance sessions for Computer Graphics to binay");
        $this->newLine();
        
        // Show all Computer Graphics sessions
        $sessions = DB::table('attendance_sessions')
            ->where('subject_id', $subject->id)
            ->orderBy('date', 'desc')
            ->get();
        
        $this->info("All Computer Graphics attendance sessions:");
        foreach ($sessions as $session) {
            $this->line("  - Session #{$session->id} - Date: {$session->date} - Teacher ID: {$session->teacher_id}");
        }
        
        $this->newLine();
        $this->info('Done! Please clear your browser cache and refresh.');
        
        return 0;
    }
}
