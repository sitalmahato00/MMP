<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $demo = new DemoDataSeeder();
        $assets = $demo->seedAssets();

        $principal = User::where('email', 'principal@mmp.edu.np')->firstOrFail();
        $department = Department::where('code', 'IT')->firstOrFail();
        $program = Program::where('code', 'DIT')->firstOrFail();

        $notices = $demo->seedNotices($principal, $department, $program, $assets);
        $demo->seedNoticeAttachments($notices, $assets);
    }
}
