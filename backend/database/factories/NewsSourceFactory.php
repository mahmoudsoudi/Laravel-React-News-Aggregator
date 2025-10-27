<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsSource>
 */
class NewsSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company() . ' News';
        return [
            'name' => $name,
            'slug' => \Str::slug($name),
            'description' => $this->faker->paragraph(2),
            'url' => $this->faker->url(),
            'api_url' => $this->faker->url(),
            'api_key' => $this->faker->uuid(),
            'api_config' => [
                'endpoints' => [
                    'headlines' => '/v2/top-headlines',
                    'everything' => '/v2/everything'
                ],
                'rate_limit' => 1000,
                'timeout' => 30
            ],
            'logo_url' => $this->faker->imageUrl(200, 200, 'logo'),
            'country' => $this->faker->countryCode(),
            'language' => $this->faker->languageCode(),
            'is_active' => true,
            'fetch_interval_minutes' => $this->faker->numberBetween(30, 120),
            'last_fetched_at' => $this->faker->optional(0.7)->dateTimeBetween('-2 hours', 'now'),
        ];
    }
}
