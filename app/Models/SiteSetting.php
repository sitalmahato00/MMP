<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key', 'group', 'label', 'value', 'type'
    ];

    public static function defaultDefinitions(): array
    {
        return [
            ['key' => 'site_logo', 'group' => 'about', 'label' => 'Site Logo (Public + Admin)', 'type' => 'image', 'value' => ''],
            ['key' => 'what_is_mmp', 'group' => 'about', 'label' => 'What is MMP', 'type' => 'richtext', 'value' => 'Manmohan Memorial Polytechnic...'],
            ['key' => 'objectives', 'group' => 'about', 'label' => 'Objectives', 'type' => 'richtext', 'value' => 'Our objectives are...'],
            ['key' => 'welcome_message', 'group' => 'about', 'label' => 'Welcome Message', 'type' => 'richtext', 'value' => 'Welcome to MMP...'],
            ['key' => 'principals_message', 'group' => 'about', 'label' => 'Principal message', 'type' => 'richtext', 'value' => 'It brings me great joy...'],
            ['key' => 'principal_photo', 'group' => 'about', 'label' => 'Principal Photo', 'type' => 'image', 'value' => ''],
            ['key' => 'president_name', 'group' => 'about', 'label' => 'President Name', 'type' => 'text', 'value' => 'Hon. President Name'],
            ['key' => 'principal_name', 'group' => 'about', 'label' => 'Principal Name', 'type' => 'text', 'value' => 'Mr. Principal Name'],
            ['key' => 'classrooms_labs', 'group' => 'facilities', 'label' => 'Classrooms & Labs', 'type' => 'richtext', 'value' => 'We offer state of the art...'],
            ['key' => 'workshops', 'group' => 'facilities', 'label' => 'Workshops', 'type' => 'richtext', 'value' => 'Our workshops include...'],
            ['key' => 'transportation', 'group' => 'facilities', 'label' => 'Transportation', 'type' => 'richtext', 'value' => 'We provide bus service...'],
            ['key' => 'scholarship_schemes', 'group' => 'student_affairs', 'label' => 'Scholarship Schemes', 'type' => 'richtext', 'value' => 'Various scholarships are...'],
            ['key' => 'internships_placements', 'group' => 'student_affairs', 'label' => 'Internships & Placements', 'type' => 'richtext', 'value' => '100% placement rate...'],
            ['key' => 'contact_us_content', 'group' => 'contact', 'label' => 'Contact Us', 'type' => 'richtext', 'value' => 'Reach out to us for admissions, academic information, facility visits, and institutional support.'],
            ['key' => 'contact_email', 'group' => 'contact', 'label' => 'Contact Email', 'type' => 'text', 'value' => 'info@mmp.edu.np'],
            ['key' => 'contact_phone', 'group' => 'contact', 'label' => 'Contact Phone', 'type' => 'text', 'value' => '+977-1-444444'],
            ['key' => 'contact_address', 'group' => 'contact', 'label' => 'Contact Address', 'type' => 'text', 'value' => 'Kathmandu, Nepal'],
            ['key' => 'google_maps_iframe', 'group' => 'contact', 'label' => 'Google Maps Embed', 'type' => 'textarea', 'value' => '<iframe...></iframe>'],
        ];
    }

    public static function managedPageDefinitions(): array
    {
        return [
            'what-is-mmp' => [
                'title' => 'What is MMP',
                'content_key' => 'what_is_mmp',
                'meta_description' => 'Learn about Manmohan Memorial Polytechnic and its institutional identity.',
            ],
            'objectives' => [
                'title' => 'Objectives',
                'content_key' => 'objectives',
                'meta_description' => 'Read the institutional objectives of Manmohan Memorial Polytechnic.',
            ],
            'contact-us' => [
                'title' => 'Contact Us',
                'content_key' => 'contact_us_content',
                'meta_description' => 'Get in touch with Manmohan Memorial Polytechnic using the official contact details.',
            ],
        ];
    }

    public static function managedPageDefinition(string $slug): ?array
    {
        return static::managedPageDefinitions()[$slug] ?? null;
    }

    public static function ensureDefaults(): void
    {
        foreach (static::defaultDefinitions() as $setting) {
            static::query()->firstOrCreate(['key' => $setting['key']], $setting);
        }

        // Backward compatibility: reuse existing President Message as Welcome Message
        // if welcome_message is still empty/default.
        $legacyValue = static::query()->where('key', 'presidents_message')->value('value');

        if ($legacyValue !== null && trim((string) $legacyValue) !== '') {
            static::query()
                ->where('key', 'welcome_message')
                ->where(function ($query) {
                    $query->whereNull('value')
                        ->orWhere('value', '')
                        ->orWhere('value', 'Welcome to MMP...');
                })
                ->update(['value' => $legacyValue]);
        }
    }
}
