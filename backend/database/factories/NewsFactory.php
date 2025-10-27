<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'content' => $this->faker->paragraphs(5, true),
            'url' => $this->faker->unique()->url(),
            'image_url' => $this->faker->imageUrl(800, 600, 'news'),
            'author' => $this->faker->name(),
            'published_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'external_id' => $this->faker->uuid(),
            'metadata' => [
                'source_url' => $this->faker->url(),
                'tags' => $this->faker->words(3),
                'language' => $this->faker->languageCode(),
            ],
            'is_active' => true,
        ];
    }
}
