<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailPasswordAuthenticationTest extends TestCase
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
     * Test complete login flow with valid email and password
     */
    public function test_user_can_login_with_valid_email_and_password(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_enabled' => false,
        ]);

        // Assign a role
        $user->assignRole('student');

        // Attempt login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
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
                        'email' => 'test@example.com',
                        'panel_type' => 'student',
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
    public function test_token_can_be_used_for_authenticated_requests(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_enabled' => false,
        ]);

        $user->assignRole('teacher');

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        // Use token to access protected endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/user');

        // Assert successful response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
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
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'test@example.com',
                        'panel_type' => 'teacher',
                    ],
                ],
            ]);
    }

    /**
     * Test user data is returned correctly
     */
    public function test_user_data_is_returned_correctly(): void
    {
        // Create a test user with specific data
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_enabled' => false,
        ]);

        $user->assignRole('hod');

        // Login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Assert user data
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'phone' => '1234567890',
                        'role' => 'hod',
                        'panel_type' => 'hod',
                    ],
                ],
            ]);

        // Assert sensitive data is not returned
        $userData = $response->json('data.user');
        $this->assertArrayNotHasKey('password', $userData);
        $this->assertArrayNotHasKey('remember_token', $userData);
    }

    /**
     * Test login fails with invalid credentials
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Attempt login with wrong password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert failed response
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test login fails with inactive account
     */
    public function test_login_fails_with_inactive_account(): void
    {
        // Create an inactive user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        // Attempt login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert failed response
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
            ]);
    }
}
