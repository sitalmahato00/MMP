<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicHomePwaShellTest extends TestCase
{
    public function test_home_page_exposes_pwa_shell_markup(): void
    {
        $html = view('layouts.guest', [
            'publicCourses' => collect(),
        ])->render();

        $this->assertStringContainsString('manifest.json?v=4', $html);
        $this->assertStringContainsString('data-shell-bottom-nav', $html);
        $this->assertStringContainsString('Install MMP App', $html);
        $this->assertStringContainsString('Download', $html);
        $this->assertStringNotContainsString('id="install-modal"', $html);
    }
}
