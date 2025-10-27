<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Technology', 'Business', 'Sports', 'Health', 'Science',
            'Entertainment', 'Politics', 'World', 'Local', 'Opinion'
        ];
        $name = $this->faker->randomElement($categories);
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'];

        return [
            'name' => $name,
            'slug' => \Str::slug($name),
            'description' => $this->faker->sentence(8),
            'color' => $this->faker->randomElement($colors),
            'icon' => 'fas fa-' . $this->faker->randomElement(['newspaper', 'globe', 'chart-line', 'heart', 'microchip']),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
