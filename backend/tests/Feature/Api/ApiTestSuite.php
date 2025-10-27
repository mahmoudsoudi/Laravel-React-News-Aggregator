<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTestSuite extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Complete API workflow test - registration to deletion
     */
    public function test_complete_user_workflow()
    {
        // Step 1: Register a new user
        $userData = [
            'name' => 'Workflow Test User',
            'email' => 'workflow@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $registerResponse = $this->postJson('/api/register', $userData);
        $registerResponse->assertStatus(201)
                        ->assertJson(['success' => true]);

        $newUserToken = $registerResponse->json('data.token');

        // Step 2: Login with the new user
        $loginData = [
            'email' => 'workflow@example.com',
            'password' => 'password123'
        ];

        $loginResponse = $this->postJson('/api/login', $loginData);
        $loginResponse->assertStatus(200)
                     ->assertJson(['success' => true]);

        $loginToken = $loginResponse->json('data.token');

        // Step 3: Get user profile
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $loginToken
        ])->getJson('/api/user');

        $profileResponse->assertStatus(200)
                       ->assertJson([
                           'success' => true,
                           'data' => [
                               'user' => [
                                   'name' => 'Workflow Test User',
                                   'email' => 'workflow@example.com'
                               ]
                           ]
                       ]);

        // Step 4: Update user profile
        $updateData = [
            'name' => 'Updated Workflow User',
            'email' => 'updated-workflow@example.com'
        ];

        $updateResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $loginToken
        ])->putJson('/api/user', $updateData);

        $updateResponse->assertStatus(200)
                      ->assertJson(['success' => true]);

        // Step 5: Update password
        $passwordData = [
            'name' => 'Updated Workflow User',
            'email' => 'updated-workflow@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $passwordResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $loginToken
        ])->putJson('/api/user', $passwordData);

        $passwordResponse->assertStatus(200)
                        ->assertJson(['success' => true]);

        // Step 6: Login with new password
        $newLoginData = [
            'email' => 'updated-workflow@example.com',
            'password' => 'newpassword123'
        ];

        $newLoginResponse = $this->postJson('/api/login', $newLoginData);
        $newLoginResponse->assertStatus(200)
                        ->assertJson(['success' => true]);

        $newToken = $newLoginResponse->json('data.token');

        // Step 7: Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $newToken
        ])->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
                      ->assertJson(['success' => true]);

        // Step 8: Verify access is denied after logout
        $deniedResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $newToken
        ])->getJson('/api/user');

        $deniedResponse->assertStatus(401)
                      ->assertJson(['success' => false]);

        // Step 9: Delete account (using a fresh token)
        $finalLoginResponse = $this->postJson('/api/login', $newLoginData);
        $finalToken = $finalLoginResponse->json('data.token');

        $deleteResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $finalToken
        ])->deleteJson('/api/user');

        $deleteResponse->assertStatus(200)
                      ->assertJson(['success' => true]);

        // Step 10: Verify user is deleted
        $this->assertDatabaseMissing('users', [
            'email' => 'updated-workflow@example.com'
        ]);
    }

    /**
     * Test all validation scenarios
     */
    public function test_all_validation_scenarios()
    {
        // Test registration validation
        $this->test_registration_validation();

        // Test login validation
        $this->test_login_validation();

        // Test profile update validation
        $this->test_profile_update_validation();
    }

    private function test_registration_validation()
    {
        // Missing fields
        $response = $this->postJson('/api/register', []);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);

        // Invalid email
        $response = $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Password too short
        $response = $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);

        // Password mismatch
        $response = $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);

        // Duplicate email
        User::factory()->create(['email' => 'duplicate@example.com']);
        $response = $this->postJson('/api/register', [
            'name' => 'Test',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    private function test_login_validation()
    {
        // Missing credentials
        $response = $this->postJson('/api/login', []);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);

        // Invalid email format
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Nonexistent user
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);
        $response->assertStatus(401)
                ->assertJson(['success' => false]);

        // Wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
        $response->assertStatus(401)
                ->assertJson(['success' => false]);
    }

    private function test_profile_update_validation()
    {
        // Invalid email
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', [
            'name' => 'Test',
            'email' => 'invalid-email'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Password mismatch
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'different123'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);

        // Duplicate email
        User::factory()->create(['email' => 'duplicate@example.com']);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', [
            'name' => 'Test',
            'email' => 'duplicate@example.com'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test authentication scenarios
     */
    public function test_authentication_scenarios()
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

        // Access protected route with valid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/user');
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    /**
     * Test API response format consistency
     */
    public function test_api_response_format_consistency()
    {
        // Test successful response format
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
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
            'message',
            'error'
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
