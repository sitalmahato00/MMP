<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        Role::create(['name' => 'principal', 'guard_name' => 'web']);
        Role::create(['name' => 'hod', 'guard_name' => 'web']);
        Role::create(['name' => 'teacher', 'guard_name' => 'web']);
        Role::create(['name' => 'student', 'guard_name' => 'web']);
        Role::create(['name' => 'parent', 'guard_name' => 'web']);
        Role::create(['name' => 'alumni', 'guard_name' => 'web']);
    }

    /**
     * Test login with valid email and password
     */
    public function test_login_with_valid_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_enabled' => false,
        ]);

        $user->assignRole('student');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);
    }

    /**
     * Test login with valid phone and password
     */
    public function test_login_with_valid_phone_and_password(): void
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_enabled' => false,
        ]);

        $user->assignRole('teacher');

        $response = $this->postJson('/api/auth/login', [
            'phone' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);
    }

    /**
     * Test login with valid phone and OTP
     */
    public function test_login_with_valid_phone_and_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'is_active' => true,
            'two_factor_enabled' => false,
        ]);

        $user->assignRole('parent');

        // Create OTP
        $otpCode = '123456';
        Otp::create([
            'phone' => '1234567890',
            'otp' => Hash::make($otpCode),
            'attempts' => 0,
            'expires_at' => Carbon::now()->addMinutes(1),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '1234567890',
            'otp' => $otpCode,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);
    }

    /**
     * Test login with missing credentials
     */
    public function test_login_with_missing_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test login with inactive account
     */
    public function test_login_with_inactive_account(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
            ]);
    }

    /**
     * Test login with 2FA enabled requires OTP
     */
    public function test_login_with_2fa_enabled_requires_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_enabled' => true,
            'two_factor_method' => 'email',
        ]);

        $user->assignRole('hod');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Two-factor authentication required',
                'requires_2fa' => true,
                'two_factor_method' => 'email',
            ]);
    }

    /**
     * Test rate limiting enforcement
     */
    public function test_rate_limiting_enforcement(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Make 4 requests (limit is 3 per minute)
        for ($i = 0; $i < 4; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);

            if ($i < 3) {
                $response->assertStatus(401); // Invalid credentials
            } else {
                $response->assertStatus(429); // Too many requests
            }
        }
    }
}
