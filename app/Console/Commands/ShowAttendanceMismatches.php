<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowAttendanceMismatches extends Command
{
    protected $signature = 'attendance:show-mismatches';
    protected $description = 'Show attendance sessions where teacher_id does not match the assigned teacher';

    public function handle()
    {
        $this->info('Checking for attendance sessions with mismatched teacher IDs...');
        
        $mismatches = DB::select("
            SELECT 
                a.id as session_id,
                a.teacher_id as current_teacher_id,
                t1.name as current_teacher_name,
                s.name as subject_name,
                st.teacher_id as correct_teacher_id,
                t2.name as correct_teacher_name,
                a.date
            FROM attendance_sessions a
            JOIN subjects s ON a.subject_id = s.id
            LEFT JOIN teachers curr_t ON a.teacher_id = curr_t.id
            LEFT JOIN users t1 ON curr_t.user_id = t1.id
            LEFT JOIN subject_teacher st ON a.subject_id = st.subject_id 
                AND a.academic_session_id = st.academic_session_id
            LEFT JOIN teachers corr_t ON st.teacher_id = corr_t.id
            LEFT JOIN users t2 ON corr_t.user_id = t2.id
            WHERE a.teacher_id != st.teacher_id
                OR st.teacher_id IS NULL
            ORDER BY a.date DESC
            LIMIT 20
        ");
        
        if (empty($mismatches)) {
            $this->info('✓ No mismatches found! All attendance sessions have correct teacher IDs.');
            return 0;
        }
        
        $this->warn('Found ' . count($mismatches) . ' mismatched sessions:');
        $this->newLine();
        
        $tableData = [];
        foreach ($mismatches as $m) {
            $tableData[] = [
                $m->session_id,
                $m->subject_name,
                $m->current_teacher_name ?? 'Unknown',
                $m->correct_teacher_name ?? 'No teacher assigned',
                $m->date,
            ];
        }
        
        $this->table(
            ['Session ID', 'Subject', 'Current Teacher', 'Should Be', 'Date'],
            $tableData
        );
        
        $this->newLine();
        $this->info('To fix these, run: php artisan attendance:fix-all');
        
        return 0;
    }
}
