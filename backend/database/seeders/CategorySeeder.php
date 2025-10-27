<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Latest technology news, gadgets, and innovations',
                'color' => '#3B82F6',
                'icon' => 'fas fa-microchip',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Business news, markets, and financial updates',
                'color' => '#10B981',
                'icon' => 'fas fa-chart-line',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'description' => 'Sports news, scores, and updates',
                'color' => '#F59E0B',
                'icon' => 'fas fa-football-ball',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Health',
                'slug' => 'health',
                'description' => 'Health news, medical research, and wellness',
                'color' => '#EF4444',
                'icon' => 'fas fa-heartbeat',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Science',
                'slug' => 'science',
                'description' => 'Scientific discoveries and research news',
                'color' => '#8B5CF6',
                'icon' => 'fas fa-flask',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'Politics',
                'slug' => 'politics',
                'description' => 'Political news and government updates',
                'color' => '#6B7280',
                'icon' => 'fas fa-landmark',
                'is_active' => true,
                'sort_order' => 6
            ],
            [
                'name' => 'World',
                'slug' => 'world',
                'description' => 'International news and global events',
                'color' => '#059669',
                'icon' => 'fas fa-globe',
                'is_active' => true,
                'sort_order' => 7
            ],
            [
                'name' => 'Entertainment',
                'slug' => 'entertainment',
                'description' => 'Entertainment news, movies, and celebrities',
                'color' => '#EC4899',
                'icon' => 'fas fa-film',
                'is_active' => true,
                'sort_order' => 8
            ],
            [
                'name' => 'Environment',
                'slug' => 'environment',
                'description' => 'Environmental news and climate updates',
                'color' => '#22C55E',
                'icon' => 'fas fa-leaf',
                'is_active' => true,
                'sort_order' => 9
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'Education news and academic updates',
                'color' => '#6366F1',
                'icon' => 'fas fa-graduation-cap',
                'is_active' => true,
                'sort_order' => 10
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }
    }
}
