<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $demo = new DemoDataSeeder();
        $assets = $demo->seedAssets();

        $demo->seedBanners($assets);
    }
}
