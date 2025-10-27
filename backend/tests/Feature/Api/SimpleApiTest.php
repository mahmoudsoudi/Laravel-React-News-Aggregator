<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimpleApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_health_check()
    {
        $response = $this->getJson('/api/test');
        $response->assertStatus(200)
                ->assertJson(['message' => 'API is working!']);
    }

    public function test_user_registration_and_login_flow()
    {
        // Register a new user
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $registerResponse = $this->postJson('/api/register', $userData);
        $registerResponse->assertStatus(201)
                        ->assertJson(['success' => true]);

        $token = $registerResponse->json('data.token');

        // Login with the same credentials
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $loginResponse = $this->postJson('/api/login', $loginData);
        $loginResponse->assertStatus(200)
                     ->assertJson(['success' => true]);

        $loginToken = $loginResponse->json('data.token');

        // Access protected route
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $loginToken
        ])->getJson('/api/user');

        $profileResponse->assertStatus(200)
                       ->assertJson(['success' => true]);

        // Update profile
        $updateData = [
            'name' => 'Updated User',
            'email' => 'updated@example.com'
        ];

        $updateResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $loginToken
        ])->putJson('/api/user', $updateData);

        $updateResponse->assertStatus(200)
                      ->assertJson(['success' => true]);

        // Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $loginToken
        ])->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
    }

    public function test_validation_errors()
    {
        // Test registration validation
        $response = $this->postJson('/api/register', []);
        $response->assertStatus(422)
                ->assertJsonStructure(['success', 'message', 'errors']);

        // Test login validation
        $response = $this->postJson('/api/login', []);
        $response->assertStatus(422)
                ->assertJsonStructure(['success', 'message', 'errors']);

        // Test invalid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]);
        $response->assertStatus(401)
                ->assertJson(['success' => false]);
    }

    public function test_authentication_required()
    {
        // Access protected route without token
        $response = $this->getJson('/api/user');
        $response->assertStatus(401)
                ->assertJson(['success' => false]);

        // Access protected route with invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->getJson('/api/user');
        $response->assertStatus(401)
                ->assertJson(['success' => false]);
    }

    public function test_response_format_consistency()
    {
        // Test successful response format
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/user');

        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        // Test error response format
        $response = $this->getJson('/api/user');
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        // Test validation error response format
        $response = $this->postJson('/api/register', []);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors'
        ]);
    }
}
