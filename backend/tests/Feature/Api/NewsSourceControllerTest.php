<?php

namespace Tests\Feature\Api;

use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsSourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $newsSource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->newsSource = NewsSource::factory()->create([
            'name' => 'Test News Source',
            'slug' => 'test-source',
            'description' => 'A test news source',
            'url' => 'https://example.com',
            'api_url' => 'https://api.example.com',
            'is_active' => true
        ]);
    }

    public function test_get_news_sources_index()
    {
        $response = $this->getJson('/api/sources');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'sources' => [
                            '*' => [
                                'id',
                                'name',
                                'slug',
                                'description',
                                'url',
                                'api_url',
                                'logo_url',
                                'country',
                                'language',
                                'is_active'
                            ]
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'News sources retrieved successfully'
                ]);
    }

    public function test_get_news_sources_only_active()
    {
        // Create an inactive source
        NewsSource::factory()->create([
            'name' => 'Inactive Source',
            'slug' => 'inactive-source',
            'is_active' => false
        ]);

        $response = $this->getJson('/api/sources');

        $response->assertStatus(200);

        $sources = $response->json('data.sources');
        $this->assertCount(1, $sources);
        $this->assertEquals('Test News Source', $sources[0]['name']);
    }

    public function test_get_news_source_show()
    {
        $response = $this->getJson('/api/sources/test-source');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'source' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'url',
                            'api_url',
                            'logo_url',
                            'country',
                            'language',
                            'is_active'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'source' => [
                            'id' => $this->newsSource->id,
                            'name' => 'Test News Source',
                            'slug' => 'test-source'
                        ]
                    ]
                ]);
    }

    public function test_get_news_source_show_not_found()
    {
        $response = $this->getJson('/api/sources/nonexistent');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Failed to retrieve news source: No query results for model [App\\Models\\NewsSource].'
                ]);
    }

    public function test_get_news_source_show_inactive()
    {
        // Create an inactive source
        $inactiveSource = NewsSource::factory()->create([
            'name' => 'Inactive Source',
            'slug' => 'inactive-source',
            'is_active' => false
        ]);

        $response = $this->getJson('/api/sources/inactive-source');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false
                ]);
    }
}
