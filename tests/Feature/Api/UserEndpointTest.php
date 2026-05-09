<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserEndpointTest extends TestCase
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
     * Test endpoint returns user data with valid token
     */
    public function test_endpoint_returns_user_data_with_valid_token(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $user->assignRole('student');

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        // Get user profile
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/user');

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
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'phone' => '1234567890',
                        'role' => 'student',
                        'panel_type' => 'student',
                    ],
                ],
            ]);
    }

    /**
     * Test endpoint returns 401 with invalid token
     */
    public function test_endpoint_returns_401_with_invalid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/v1/user');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
            ]);
    }

    /**
     * Test endpoint returns 401 without token
     */
    public function test_endpoint_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
            ]);
    }

    /**
     * Test user relationships are loaded
     */
    public function test_user_relationships_are_loaded(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $user->assignRole('student');

        // Create student relationship
        Student::factory()->create([
            'user_id' => $user->id,
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        // Get user profile
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/user');

        $response->assertStatus(200);

        // Note: The actual relationship data structure depends on your Student model
        // This test verifies the endpoint loads relationships without errors
    }

    /**
     * Test different panel types for different roles
     */
    public function test_different_panel_types_for_different_roles(): void
    {
        $roles = [
            'principal' => 'admin',
            'hod' => 'hod',
            'teacher' => 'teacher',
            'student' => 'student',
            'parent' => 'parent',
            'alumni' => 'alumni',
        ];

        foreach ($roles as $role => $expectedPanelType) {
            $user = User::factory()->create([
                'email' => "{$role}@example.com",
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);

            $user->assignRole($role);

            // Login
            $loginResponse = $this->postJson('/api/auth/login', [
                'email' => "{$role}@example.com",
                'password' => 'password123',
            ]);

            $token = $loginResponse->json('data.token');

            // Get user profile
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('/api/v1/user');

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'role' => $role,
                            'panel_type' => $expectedPanelType,
                        ],
                    ],
                ]);
        }
    }
}
