<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicManagedPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_managed_what_is_mmp_page_uses_site_settings_content(): void
    {
        SiteSetting::ensureDefaults();

        SiteSetting::query()->where('key', 'what_is_mmp')->update([
            'value' => 'Managed MMP copy from web settings.',
        ]);

        Page::create([
            'title' => 'Legacy What is MMP',
            'slug' => 'what-is-mmp',
            'content' => 'Legacy page table content.',
            'is_published' => true,
        ]);

        $response = $this->get('/page/what-is-mmp');

        $response->assertOk();
        $response->assertSee('Managed MMP copy from web settings.');
        $response->assertDontSee('Legacy page table content.');
    }

    public function test_contact_us_page_uses_web_settings_content_and_details(): void
    {
        SiteSetting::ensureDefaults();

        SiteSetting::query()->where('key', 'contact_us_content')->update([
            'value' => '<p>Reach us for admissions and support.</p>',
        ]);
        SiteSetting::query()->where('key', 'contact_email')->update([
            'value' => 'hello@example.test',
        ]);
        SiteSetting::query()->where('key', 'contact_phone')->update([
            'value' => '+977-123456',
        ]);
        SiteSetting::query()->where('key', 'contact_address')->update([
            'value' => 'Biratnagar, Nepal',
        ]);
        SiteSetting::query()->where('key', 'google_maps_iframe')->update([
            'value' => '<iframe src="https://maps.example.test/embed"></iframe>',
        ]);

        $response = $this->get('/page/contact-us');

        $response->assertOk();
        $response->assertSee('Reach us for admissions and support.');
        $response->assertSee('hello@example.test');
        $response->assertSee('+977-123456');
        $response->assertSee('Biratnagar, Nepal');
        $response->assertSee('https://maps.example.test/embed', false);
    }

    public function test_regular_cms_pages_still_use_pages_table(): void
    {
        Page::create([
            'title' => 'Gallery',
            'slug' => 'gallery',
            'content' => 'Gallery content from pages table.',
            'is_published' => true,
        ]);

        $response = $this->get('/page/gallery');

        $response->assertOk();
        $response->assertSee('Gallery content from pages table.');
    }
}
