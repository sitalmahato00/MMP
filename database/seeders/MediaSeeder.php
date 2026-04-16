<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $demo = new DemoDataSeeder();
        $assets = $demo->seedAssets();

        $principal = User::where('email', 'principal@mmp.edu.np')->firstOrFail();
        $department = Department::where('code', 'IT')->firstOrFail();

        $demo->seedMedia($principal, $department, $assets);
    }
}
