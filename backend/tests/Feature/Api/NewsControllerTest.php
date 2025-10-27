<?php

namespace Tests\Feature\Api;

use App\Models\News;
use App\Models\NewsSource;
use App\Models\Category;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NewsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $newsSource;
    protected $category;
    protected $news;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
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

        $this->news = News::factory()->create([
            'title' => 'Test News Article',
            'description' => 'This is a test news article',
            'url' => 'https://example.com/news/1',
            'news_source_id' => $this->newsSource->id,
            'category_id' => $this->category->id,
            'published_at' => now(),
            'is_active' => true
        ]);

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_get_news_index()
    {
        $response = $this->getJson('/api/news');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'news',
                        'pagination' => [
                            'current_page',
                            'last_page',
                            'per_page',
                            'total',
                            'from',
                            'to'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'News retrieved successfully'
                ]);
    }

    public function test_get_news_with_filters()
    {
        // Test category filter
        $response = $this->getJson('/api/news?category=' . $this->category->id);
        $response->assertStatus(200);

        // Test source filter
        $response = $this->getJson('/api/news?source=' . $this->newsSource->id);
        $response->assertStatus(200);

        // Test search
        $response = $this->getJson('/api/news?search=test');
        $response->assertStatus(200);

        // Test days filter
        $response = $this->getJson('/api/news?days=7');
        $response->assertStatus(200);

        // Test pagination
        $response = $this->getJson('/api/news?per_page=5');
        $response->assertStatus(200);
    }

    public function test_get_news_show()
    {
        $response = $this->getJson('/api/news/' . $this->news->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'news' => [
                            'id',
                            'title',
                            'description',
                            'url',
                            'news_source',
                            'category'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'news' => [
                            'id' => $this->news->id,
                            'title' => 'Test News Article'
                        ]
                    ]
                ]);
    }

    public function test_get_news_show_not_found()
    {
        $response = $this->getJson('/api/news/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false
                ]);
    }

    public function test_get_trending_news()
    {
        $response = $this->getJson('/api/news/trending');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'news'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Trending news retrieved successfully'
                ]);
    }

    public function test_get_news_by_category()
    {
        $response = $this->getJson('/api/news/category/technology');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'category',
                        'news',
                        'pagination'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'category' => [
                            'slug' => 'technology'
                        ]
                    ]
                ]);
    }

    public function test_get_news_by_category_not_found()
    {
        $response = $this->getJson('/api/news/category/nonexistent');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false
                ]);
    }

    public function test_get_news_by_source()
    {
        $response = $this->getJson('/api/news/source/test-source');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'source',
                        'news',
                        'pagination'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'source' => [
                            'slug' => 'test-source'
                        ]
                    ]
                ]);
    }

    public function test_get_news_by_source_not_found()
    {
        $response = $this->getJson('/api/news/source/nonexistent');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false
                ]);
    }

    public function test_search_news()
    {
        $response = $this->getJson('/api/news/search?q=test');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'search_term',
                        'news',
                        'pagination'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'search_term' => 'test'
                    ]
                ]);
    }

    public function test_search_news_missing_query()
    {
        $response = $this->getJson('/api/news/search');

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Search term is required'
                ]);
    }

    public function test_news_with_user_preferences()
    {
        // Create user preferences
        $preferences = UserPreference::create([
            'user_id' => $this->user->id,
            'preferred_sources' => [$this->newsSource->id],
            'preferred_categories' => [$this->category->id],
            'language' => 'en'
        ]);

        // Test with authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/news');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_news_pagination()
    {
        // Create multiple news articles
        News::factory()->count(25)->create([
            'news_source_id' => $this->newsSource->id,
            'category_id' => $this->category->id,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/news?per_page=10');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'pagination' => [
                            'current_page',
                            'last_page',
                            'per_page',
                            'total'
                        ]
                    ]
                ]);

        $data = $response->json('data');
        $this->assertEquals(10, $data['pagination']['per_page']);
        $this->assertGreaterThan(1, $data['pagination']['last_page']);
    }
}
