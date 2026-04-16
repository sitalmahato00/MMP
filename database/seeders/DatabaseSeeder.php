<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SiteSettingSeeder::class,
            DemoDataSeeder::class,
        ]);

        $this->command->info('Database seeded successfully.');
        $this->command->info('Demo accounts use the password: password');
    }
}
