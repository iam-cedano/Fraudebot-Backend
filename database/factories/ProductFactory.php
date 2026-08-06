<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Database\Factories\Support\ScamEnterprisePool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $market = fake()->randomElement(ScamEnterprisePool::markets());

        return [
            'category_id' => Category::firstOrCreate(
                ['name' => $market],
                ['emoji' => fake()->randomElement(['📦', '🏷️', '🛍️'])],
            )->id,
            'name' => $market,
            'emoji' => fake()->randomElement(['🛒', '💳', '📱', '🎮', '👟']),
        ];
    }
}
