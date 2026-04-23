<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAttendanceSession extends Command
{
    protected $signature = 'attendance:check {session_id}';
    protected $description = 'Check details of a specific attendance session';

    public function handle()
    {
        $sessionId = $this->argument('session_id');
        
        $session = DB::table('attendance_sessions as a')
            ->join('subjects as s', 'a.subject_id', '=', 's.id')
            ->join('teachers as t', 'a.teacher_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('a.id', $sessionId)
            ->select('a.*', 's.name as subject_name', 'u.name as teacher_name', 't.id as teacher_id')
            ->first();
        
        if (!$session) {
            $this->error("Attendance session #{$sessionId} not found!");
            return 1;
        }
        
        $this->info("Attendance Session #{$sessionId}");
        $this->info("Subject: {$session->subject_name}");
        $this->info("Current Teacher: {$session->teacher_name} (ID: {$session->teacher_id})");
        $this->info("Date: {$session->date}");
        $this->newLine();
        
        // Get all teachers assigned to this subject
        $teachers = DB::table('subject_teacher as st')
            ->join('teachers as t', 'st.teacher_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('st.subject_id', $session->subject_id)
            ->where('st.academic_session_id', $session->academic_session_id)
            ->select('t.id', 'u.name')
            ->get();
        
        $this->info("Teachers assigned to '{$session->subject_name}':");
        foreach ($teachers as $teacher) {
            $marker = ($teacher->id == $session->teacher_id) ? ' ← Current' : '';
            $this->line("  - {$teacher->name} (ID: {$teacher->id}){$marker}");
        }
        
        return 0;
    }
}
