<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class PublicMobilePreviewTest extends TestCase
{
    public function test_mobile_app_preview_route_is_registered(): void
    {
        $route = app('router')->getRoutes()->match(
            Request::create('/app-preview', 'GET')
        );

        $this->assertSame('public.app-preview', $route->getName());
        $this->assertStringContainsString('MobilePreviewController', $route->getActionName());
        $this->assertSame(url('/app-preview'), route('public.app-preview'));
    }
}
