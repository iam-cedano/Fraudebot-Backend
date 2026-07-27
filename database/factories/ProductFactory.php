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
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::create([
                'name' => fake()->unique()->words(2, true),
                'emoji' => fake()->randomElement(['📦', '🏷️', '🛍️']),
            ])->id,
            'name' => fake()->words(3, true),
            'emoji' => fake()->randomElement(['🛒', '💳', '📱', '🎮', '👟']),
        ];
    }
}
