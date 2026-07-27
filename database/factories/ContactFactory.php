<?php

namespace Database\Factories;

use App\Domain\Contact\Enums\PlatformType;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\Scammer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'scammer_id' => Scammer::factory(),
            'name' => $this->faker->firstName(),
            'platform' => $this->faker->randomElement(PlatformType::cases()),
            'contact' => $this->faker->unique()->safeEmail(),
            'is_active' => true
        ];
    }
}
