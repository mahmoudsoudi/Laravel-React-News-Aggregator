<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create([
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Latest technology news',
            'color' => '#3B82F6',
            'icon' => 'fas fa-microchip',
            'is_active' => true,
            'sort_order' => 1
        ]);
    }

    public function test_get_categories_index()
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'categories' => [
                            '*' => [
                                'id',
                                'name',
                                'slug',
                                'description',
                                'color',
                                'icon',
                                'is_active',
                                'sort_order'
                            ]
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Categories retrieved successfully'
                ]);
    }

    public function test_get_categories_only_active()
    {
        // Create an inactive category
        Category::factory()->create([
            'name' => 'Inactive Category',
            'slug' => 'inactive-category',
            'is_active' => false
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $this->assertCount(1, $categories);
        $this->assertEquals('Technology', $categories[0]['name']);
    }

    public function test_get_categories_ordered()
    {
        // Create categories with different sort orders
        Category::factory()->create([
            'name' => 'Business',
            'slug' => 'business',
            'sort_order' => 3
        ]);

        Category::factory()->create([
            'name' => 'Sports',
            'slug' => 'sports',
            'sort_order' => 2
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);

        $categories = $response->json('data.categories');
        $this->assertCount(3, $categories);
        $this->assertEquals('Technology', $categories[0]['name']); // sort_order = 1
        $this->assertEquals('Sports', $categories[1]['name']); // sort_order = 2
        $this->assertEquals('Business', $categories[2]['name']); // sort_order = 3
    }

    public function test_get_category_show()
    {
        $response = $this->getJson('/api/categories/technology');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'category' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'color',
                            'icon',
                            'is_active',
                            'sort_order'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'category' => [
                            'id' => $this->category->id,
                            'name' => 'Technology',
                            'slug' => 'technology'
                        ]
                    ]
                ]);
    }

    public function test_get_category_show_not_found()
    {
        $response = $this->getJson('/api/categories/nonexistent');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Failed to retrieve category: No query results for model [App\\Models\\Category].'
                ]);
    }

    public function test_get_category_show_inactive()
    {
        // Create an inactive category
        $inactiveCategory = Category::factory()->create([
            'name' => 'Inactive Category',
            'slug' => 'inactive-category',
            'is_active' => false
        ]);

        $response = $this->getJson('/api/categories/inactive-category');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false
                ]);
    }
}
