<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\HomeController;
use App\Models\Executive;
use App\Models\SiteSetting;
use App\Services\PublicDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLandingWebSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_view_uses_welcome_message_and_principal_message_from_web_settings(): void
    {
        SiteSetting::ensureDefaults();

        $welcomeMessage = 'Welcome text managed from web settings. ' . str_repeat('This is additional homepage welcome content. ', 10);

        SiteSetting::query()->where('key', 'welcome_message')->update([
            'value' => $welcomeMessage,
        ]);
        SiteSetting::query()->where('key', 'principals_message')->update([
            'value' => 'Principal note managed from web settings.',
        ]);
        SiteSetting::query()->where('key', 'principal_name')->update([
            'value' => '- Er. Sudip Adhikary',
        ]);
        SiteSetting::query()->where('key', 'principal_photo')->update([
            'value' => 'site-settings/principal.jpg',
        ]);

        Executive::query()->create([
            'name' => 'Dr. Principal',
            'type' => 'principal',
            'designation' => 'Principal',
            'is_current' => true,
            'message' => 'Legacy executive message.',
            'order' => 1,
        ]);

        PublicDataService::invalidate('*');

        $view = app(HomeController::class)->index();
        $data = $view->getData();

        $this->assertSame('public.home', $view->name());
        $this->assertSame($welcomeMessage, $data['siteSettings']->get('what_is_mmp')->value);
        $this->assertSame('Er. Sudip Adhikary', $data['leadership']['principals']->firstWhere('is_current', true)->name);
        $this->assertSame('Principal note managed from web settings.', $data['leadership']['principals']->firstWhere('is_current', true)->message);
        $this->assertSame('site-settings/principal.jpg', $data['leadership']['principals']->firstWhere('is_current', true)->avatar);

        $rendered = $view->render();
        $this->assertStringContainsString($welcomeMessage, $rendered);
    }

    public function test_legacy_president_message_is_migrated_to_welcome_message_when_needed(): void
    {
        SiteSetting::query()->create([
            'key' => 'presidents_message',
            'group' => 'about',
            'label' => 'President message',
            'type' => 'richtext',
            'value' => 'Legacy welcome from president message.',
        ]);

        SiteSetting::ensureDefaults();

        $this->assertSame(
            'Legacy welcome from president message.',
            SiteSetting::query()->where('key', 'welcome_message')->value('value')
        );
    }
}
