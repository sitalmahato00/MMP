<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SiteSettingFilesSeeder extends Seeder
{
    public function run(): void
    {
        $demo = new DemoDataSeeder();
        $assets = $demo->seedAssets();

        $demo->seedSiteSettingFiles($assets);
    }
}
