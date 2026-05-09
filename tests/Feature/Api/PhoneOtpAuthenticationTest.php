<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhoneOtpAuthenticationTest extends TestCase
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
     * Test sendOtp endpoint sends OTP to phone
     */
    public function test_send_otp_endpoint_sends_otp_to_phone(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        // Send OTP
        $response = $this->postJson('/api/auth/send-otp', [
            'phone' => '1234567890',
        ]);

        // Assert successful response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'expires_in',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'OTP sent successfully',
            ]);

        // Assert OTP was created in database
        $this->assertDatabaseHas('otps', [
            'phone' => '1234567890',
        ]);
    }

    /**
     * Test verifyOtp endpoint validates OTP and issues token
     */
    public function test_verify_otp_endpoint_validates_otp_and_issues_token(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'phone' => '1234567890',
            'is_active' => true,
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

        // Verify OTP
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone' => '1234567890',
            'otp' => $otpCode,
        ]);

        // Assert successful response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'avatar_url',
                        'role',
                        'panel_type',
                    ],
                    'token',
                    'token_type',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'phone' => '1234567890',
                        'panel_type' => 'parent',
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        // Assert token is created
        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test token can be used for authenticated requests
     */
    public function test_token_from_otp_can_be_used_for_authenticated_requests(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        $user->assignRole('student');

        // Create OTP
        $otpCode = '123456';
        Otp::create([
            'phone' => '1234567890',
            'otp' => Hash::make($otpCode),
            'attempts' => 0,
            'expires_at' => Carbon::now()->addMinutes(1),
        ]);

        // Verify OTP to get token
        $loginResponse = $this->postJson('/api/auth/verify-otp', [
            'phone' => '1234567890',
            'otp' => $otpCode,
        ]);

        $token = $loginResponse->json('data.token');

        // Use token to access protected endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/user');

        // Assert successful response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'phone' => '1234567890',
                        'panel_type' => 'student',
                    ],
                ],
            ]);
    }

    /**
     * Test send OTP fails for non-existent user
     */
    public function test_send_otp_fails_for_non_existent_user(): void
    {
        // Attempt to send OTP to non-existent phone
        $response = $this->postJson('/api/auth/send-otp', [
            'phone' => '9999999999',
        ]);

        // Assert failed response
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No account found with this phone number',
            ]);
    }

    /**
     * Test verify OTP fails with invalid OTP
     */
    public function test_verify_otp_fails_with_invalid_otp(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        // Create OTP
        $otpCode = '123456';
        Otp::create([
            'phone' => '1234567890',
            'otp' => Hash::make($otpCode),
            'attempts' => 0,
            'expires_at' => Carbon::now()->addMinutes(1),
        ]);

        // Verify with wrong OTP
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone' => '1234567890',
            'otp' => '999999',
        ]);

        // Assert failed response
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid OTP.',
            ]);
    }

    /**
     * Test verify OTP fails with expired OTP
     */
    public function test_verify_otp_fails_with_expired_otp(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        // Create expired OTP
        $otpCode = '123456';
        Otp::create([
            'phone' => '1234567890',
            'otp' => Hash::make($otpCode),
            'attempts' => 0,
            'expires_at' => Carbon::now()->subMinutes(1), // Expired
        ]);

        // Verify OTP
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone' => '1234567890',
            'otp' => $otpCode,
        ]);

        // Assert failed response
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ]);
    }
}
