<?php

namespace Database\Factories;

use App\Models\Scammer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScammerFactory extends Factory
{
    protected $model = Scammer::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'country' => $this->faker->countryCode(),
            'is_active' => $this->faker->boolean()
        ];
    }
}
