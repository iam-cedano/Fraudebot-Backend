<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Report;
use App\Models\Scammer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'product_id' => null,
            'user_id' => null,
            'organization_id' => Organization::factory(),
            'scammer_id' => Scammer::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'was_sucessful' => false,
            'is_active' => true,
        ];
    }
}
