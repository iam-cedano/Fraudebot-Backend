<?php

namespace Database\Factories;

use App\Domain\Contact\Enums\PlatformType;
use App\Models\Contact;
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
            'name' => $this->faker->firstName(),
            'platform' => $this->faker->randomElement(PlatformType::cases()),
            'reference' => $this->faker->unique()->safeEmail(),
            'is_active' => true,
        ];
    }
}
