<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\HomeController;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Services\PublicDataService;
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

        $page = app(PublicDataService::class)->getPage('what-is-mmp');

        $this->assertSame('What is MMP', $page->title);
        $this->assertSame('Managed MMP copy from web settings.', $page->content);
        $this->assertFalse($page->exists);
    }

    public function test_managed_objectives_page_uses_site_settings_content(): void
    {
        SiteSetting::ensureDefaults();

        SiteSetting::query()->where('key', 'objectives')->update([
            'value' => 'Objective copy managed from web settings.',
        ]);

        Page::create([
            'title' => 'Legacy Objectives',
            'slug' => 'objectives',
            'content' => 'Legacy objectives content.',
            'is_published' => true,
        ]);

        $page = app(PublicDataService::class)->getPage('objectives');

        $this->assertSame('Objectives', $page->title);
        $this->assertSame('Objective copy managed from web settings.', $page->content);
        $this->assertFalse($page->exists);
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

        $page = app(PublicDataService::class)->getPage('contact-us');
        $view = app(HomeController::class)->page('contact-us');
        $data = $view->getData();

        $this->assertSame('Contact Us', $page->title);
        $this->assertStringContainsString('Reach us for admissions and support.', $page->content);
        $this->assertSame('public.content-page', $view->name());
        $this->assertSame('hello@example.test', $data['siteSettings']->get('contact_email')->value);
        $this->assertSame('+977-123456', $data['siteSettings']->get('contact_phone')->value);
        $this->assertSame('Biratnagar, Nepal', $data['siteSettings']->get('contact_address')->value);
        $this->assertSame('<iframe src="https://maps.example.test/embed"></iframe>', $data['siteSettings']->get('google_maps_iframe')->value);
    }

    public function test_regular_cms_pages_still_use_pages_table(): void
    {
        Page::create([
            'title' => 'Gallery',
            'slug' => 'gallery',
            'content' => 'Gallery content from pages table.',
            'is_published' => true,
        ]);

        $page = app(PublicDataService::class)->getPage('gallery');

        $this->assertSame('Gallery', $page->title);
        $this->assertSame('Gallery content from pages table.', $page->content);
        $this->assertTrue($page->exists);
    }
}
