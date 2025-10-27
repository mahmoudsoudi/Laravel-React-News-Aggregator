<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_get_user_profile_success()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'id' => $this->user->id,
                            'name' => 'Test User',
                            'email' => 'test@example.com'
                        ]
                    ]
                ]);
    }

    public function test_get_user_profile_without_token()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error' => 'Authentication required'
                ]);
    }

    public function test_update_user_profile_success()
    {
        $updateData = [
            'name' => 'Updated Test User',
            'email' => 'updated@example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => [
                        'user' => [
                            'name' => 'Updated Test User',
                            'email' => 'updated@example.com'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Test User',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_update_user_password_success()
    {
        $updateData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);

        // Verify password was updated
        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));
    }

    public function test_update_user_profile_validation_errors()
    {
        // Test invalid email
        $updateData = [
            'name' => 'Test User',
            'email' => 'invalid-email'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Test password mismatch
        $updateData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'different123'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);

        // Test duplicate email
        User::factory()->create(['email' => 'duplicate@example.com']);

        $updateData = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/user', $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_update_user_profile_without_token()
    {
        $updateData = [
            'name' => 'Updated Test User',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson('/api/user', $updateData);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error' => 'Authentication required'
                ]);
    }

    public function test_delete_user_account_success()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/user');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Account deleted successfully'
                ]);

        // Verify user is deleted
        $this->assertDatabaseMissing('users', [
            'id' => $this->user->id
        ]);
    }

    public function test_delete_user_account_without_token()
    {
        $response = $this->deleteJson('/api/user');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error' => 'Authentication required'
                ]);
    }

    public function test_logout_functionality()
    {
        // Test logout with valid token
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
                      ->assertJson([
                          'success' => true,
                          'message' => 'Logged out successfully'
                      ]);

        // Verify the token was actually deleted from database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'test-token'
        ]);
    }
}
