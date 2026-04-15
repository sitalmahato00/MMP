<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // About Section
            ['key' => 'what_is_mmp', 'group' => 'about', 'label' => 'What is MMP', 'type' => 'richtext', 'value' => 'Manmohan Memorial Polytechnic...'],
            ['key' => 'objectives', 'group' => 'about', 'label' => 'Objectives', 'type' => 'richtext', 'value' => 'Our objectives are...'],
            ['key' => 'presidents_message', 'group' => 'about', 'label' => 'President message', 'type' => 'richtext', 'value' => 'Welcome to MMP...'],
            ['key' => 'principals_message', 'group' => 'about', 'label' => 'Principal message', 'type' => 'richtext', 'value' => 'It brings me great joy...'],
            ['key' => 'president_name', 'group' => 'about', 'label' => 'President Name', 'type' => 'text', 'value' => 'Hon. President Name'],
            ['key' => 'principal_name', 'group' => 'about', 'label' => 'Principal Name', 'type' => 'text', 'value' => 'Mr. Principal Name'],

            // Facilities
            ['key' => 'classrooms_labs', 'group' => 'facilities', 'label' => 'Classrooms & Labs', 'type' => 'richtext', 'value' => 'We offer state of the art...'],
            ['key' => 'workshops', 'group' => 'facilities', 'label' => 'Workshops', 'type' => 'richtext', 'value' => 'Our workshops include...'],
            ['key' => 'transportation', 'group' => 'facilities', 'label' => 'Transportation', 'type' => 'richtext', 'value' => 'We provide bus service...'],

            // Academics / Student Affairs
            ['key' => 'scholarship_schemes', 'group' => 'student_affairs', 'label' => 'Scholarship Schemes', 'type' => 'richtext', 'value' => 'Various scholarships are...'],
            ['key' => 'internships_placements', 'group' => 'student_affairs', 'label' => 'Internships & Placements', 'type' => 'richtext', 'value' => '100% placement rate...'],

            // Contact
            ['key' => 'contact_email', 'group' => 'contact', 'label' => 'Contact Email', 'type' => 'text', 'value' => 'info@mmp.edu.np'],
            ['key' => 'contact_phone', 'group' => 'contact', 'label' => 'Contact Phone', 'type' => 'text', 'value' => '+977-1-444444'],
            ['key' => 'contact_address', 'group' => 'contact', 'label' => 'Contact Address', 'type' => 'text', 'value' => 'Kathmandu, Nepal'],
            ['key' => 'google_maps_iframe', 'group' => 'contact', 'label' => 'Google Maps Embed', 'type' => 'textarea', 'value' => '<iframe...></iframe>'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
