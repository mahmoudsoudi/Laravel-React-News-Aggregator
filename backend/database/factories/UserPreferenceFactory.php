<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language' => $this->faker->randomElement(['en', 'es', 'fr', 'de']),
            'country' => $this->faker->countryCode(),
            'items_per_page' => $this->faker->randomElement([10, 20, 50, 100]),
            'show_images' => $this->faker->boolean(80),
            'auto_refresh' => $this->faker->boolean(30),
            'refresh_interval_minutes' => $this->faker->randomElement([5, 10, 15, 30, 60]),
            'preferred_sources' => [],
            'excluded_sources' => [],
            'preferred_categories' => [],
            'excluded_categories' => [],
            'notification_settings' => [
                'email' => $this->faker->boolean(60),
                'push' => $this->faker->boolean(40),
                'breaking_news' => $this->faker->boolean(70),
                'digest' => $this->faker->boolean(50),
            ],
        ];
    }
}
