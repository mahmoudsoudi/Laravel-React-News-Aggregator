<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserPreference;
use App\Models\NewsSource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $newsSource;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;

        $this->newsSource = NewsSource::factory()->create([
            'name' => 'Test Source',
            'slug' => 'test-source',
            'is_active' => true
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Technology',
            'slug' => 'technology',
            'is_active' => true
        ]);
    }

    public function test_get_user_preferences()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/preferences');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'preferences',
                        'available_sources',
                        'available_categories'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'User preferences retrieved successfully'
                ]);
    }

    public function test_get_user_preferences_creates_default()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/preferences');

        $response->assertStatus(200);

        // Check if preferences were created
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->id,
            'language' => 'en',
            'items_per_page' => 20
        ]);
    }

    public function test_get_user_preferences_requires_authentication()
    {
        $response = $this->getJson('/api/preferences');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_update_user_preferences()
    {
        $preferences = UserPreference::create([
            'user_id' => $this->user->id,
            'language' => 'en',
            'items_per_page' => 20
        ]);

        $updateData = [
            'preferred_sources' => [$this->newsSource->id],
            'preferred_categories' => [$this->category->id],
            'language' => 'es',
            'items_per_page' => 50,
            'show_images' => false,
            'auto_refresh' => true,
            'refresh_interval_minutes' => 15
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/preferences', $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'preferences'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'User preferences updated successfully'
                ]);

        // Verify database update
        $preferences->refresh();
        $this->assertEquals([$this->newsSource->id], $preferences->preferred_sources);
        $this->assertEquals([$this->category->id], $preferences->preferred_categories);
        $this->assertEquals('es', $preferences->language);
        $this->assertEquals(50, $preferences->items_per_page);
    }

    public function test_update_user_preferences_validation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/preferences', [
            'preferred_sources' => [999], // Non-existent source
            'preferred_categories' => [999], // Non-existent category
            'items_per_page' => 200, // Too high
            'refresh_interval_minutes' => 2000 // Too high
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'preferred_sources.0',
                    'preferred_categories.0',
                    'items_per_page',
                    'refresh_interval_minutes'
                ]);
    }

    public function test_add_preferred_source()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/preferences/sources', [
            'source_id' => $this->newsSource->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Preferred source added successfully'
                ]);

        // Verify source was added
        $preferences = $this->user->getPreferences();
        $this->assertContains($this->newsSource->id, $preferences->preferred_sources);
    }

    public function test_remove_preferred_source()
    {
        // First add a source
        $preferences = $this->user->getPreferences();
        $preferences->addPreferredSource($this->newsSource->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/preferences/sources', [
            'source_id' => $this->newsSource->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Preferred source removed successfully'
                ]);

        // Verify source was removed
        $preferences->refresh();
        $this->assertNotContains($this->newsSource->id, $preferences->preferred_sources);
    }

    public function test_add_preferred_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/preferences/categories', [
            'category_id' => $this->category->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Preferred category added successfully'
                ]);

        // Verify category was added
        $preferences = $this->user->getPreferences();
        $this->assertContains($this->category->id, $preferences->preferred_categories);
    }

    public function test_remove_preferred_category()
    {
        // First add a category
        $preferences = $this->user->getPreferences();
        $preferences->addPreferredCategory($this->category->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/preferences/categories', [
            'category_id' => $this->category->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Preferred category removed successfully'
                ]);

        // Verify category was removed
        $preferences->refresh();
        $this->assertNotContains($this->category->id, $preferences->preferred_categories);
    }

    public function test_preferences_validation_errors()
    {
        // Test invalid source ID
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/preferences/sources', [
            'source_id' => 999
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['source_id']);

        // Test invalid category ID
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/preferences/categories', [
            'category_id' => 999
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['category_id']);
    }
}
