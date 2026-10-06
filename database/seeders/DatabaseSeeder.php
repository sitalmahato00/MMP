<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            PrincipalSeeder::class,
            HodSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            ParentSeeder::class,
            StaffSeeder::class,
            AlumniSeeder::class,
        ]);

        $this->command->info('All seeders executed successfully!');
    }
}
