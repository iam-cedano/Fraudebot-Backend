<?php

namespace Database\Factories;

use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        $paymentType = $this->faker->randomElement(PaymentMethodType::cases());

        return [
            'type' => $paymentType,
            'reference' => $this->referenceFor($paymentType),
            'is_active' => true,
        ];
    }

    private function referenceFor(PaymentMethodType $type): string
    {
        return match ($type) {
            PaymentMethodType::CARD_NUMBER => $this->faker->unique()->creditCardNumber(),
            PaymentMethodType::CLABE => $this->faker->unique()->numerify('##################'),
            PaymentMethodType::ACCOUNT_NUMBER => $this->faker->unique()->numerify('##########'),
            PaymentMethodType::WALLET => '0x'.$this->faker->unique()->regexify('[a-f0-9]{40}'),
            PaymentMethodType::OTHER => $this->faker->unique()->bothify('????-########'),
        };
    }
}
