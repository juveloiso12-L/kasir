<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'slug' => fake()->unique()->slug(),
            'name' => fake()->unique()->words(2, true),
            'price' => fake()->numberBetween(1000, 100000),
            'stock' => fake()->numberBetween(0, 50),
            'image' => null,
        ];
    }
}
