<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $demo = new DemoDataSeeder();

        $principal = User::where('email', 'principal@mmp.edu.np')->firstOrFail();
        $hod = User::where('email', 'hod.it@mmp.edu.np')->firstOrFail();
        $teacher = User::where('email', 'teacher.it@mmp.edu.np')->firstOrFail();
        $student = User::where('email', 'student01@mmp.edu.np')->firstOrFail();
        $parent = User::where('email', 'parent01@mmp.edu.np')->firstOrFail();
        $alumni = User::where('email', 'alumni01@mmp.edu.np')->firstOrFail();

        $demo->seedCommunications($principal, $hod, $teacher, $student, $parent, $alumni);
    }
}
