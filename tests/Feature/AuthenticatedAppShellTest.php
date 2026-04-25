<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticatedAppShellTest extends TestCase
{
    public function test_app_layout_exposes_mobile_navigation_shell(): void
    {
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $html = view('layouts.app')->render();

        $this->assertStringContainsString('manifest.json?v=4', $html);
        $this->assertStringContainsString('data-shell-bottom-nav', $html);
        $this->assertStringContainsString('Install MMP App', $html);
        $this->assertStringContainsString('Download', $html);
        $this->assertStringContainsString('Results', $html);
    }
}
