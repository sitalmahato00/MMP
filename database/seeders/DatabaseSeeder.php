<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\PublicDataService;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SiteSettingSeeder::class,
            AcademicSeeder::class,
            SiteSettingFilesSeeder::class,
            ExecutiveSeeder::class,
            FacilitySeeder::class,
            StaffSeeder::class,
            PageSeeder::class,
            BannerSeeder::class,
            DownloadSeeder::class,
            NoticeSeeder::class,
            MediaSeeder::class,
            CommunicationSeeder::class,
            OperationsSeeder::class,
            ApplicationSeeder::class,
            // DemoDataSeeder::class,  // Uncomment for full demo data (DEMO ONLY - password: "password")
        ]);

        PublicDataService::invalidate('*');
        Cache::forget('brand:site_logo');

        $this->command->info('Database seeded successfully.');
        $this->command->info('Demo accounts use the password: password');
    }
}
