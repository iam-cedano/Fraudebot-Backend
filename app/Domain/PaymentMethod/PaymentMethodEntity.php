<?php

namespace App\Domain\PaymentMethod;

use App\Domain\Entity;

use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use function is_string;
use function intval;

class PaymentMethodEntity extends Entity {
    public function __construct(
        private mixed $type
    ) {
        parent::__construct();
    }

    protected function transform(): void {
        if (is_numeric($this->type)) {
            $paymentMethodType = PaymentMethodType::tryFrom($this->type);
        } else if (is_string($this->type)) {
            $paymentMethodTypes = array_column(PaymentMethodType::cases(), 'value', 'name');

            $paymentMethodNumber = $paymentMethodTypes[strtoupper($this->type)];
        
            $paymentMethodType = PaymentMethodType::from($paymentMethodNumber);
        }

        $this->type = $paymentMethodType;
    }

    protected function validate(): void {
        if (is_numeric($this->type)) {
            if (!PaymentMethodType::tryFrom($this->type)) {
                throw new \InvalidArgumentException('Invalid payment method type');
            }
        } elseif (is_string($this->type)) {
            $paymentMethodTypes = array_column(PaymentMethodType::cases(), 'value', 'name');

            $paymentMethodNumber = $paymentMethodTypes[strtoupper($this->type)];

            if (!$paymentMethodNumber) {
                throw new \InvalidArgumentException('Invalid payment method type');
            }
        }
    }


    public function getValue() {
        return $this->type;
    }

    public function toArray(): array {
        return [
            'type' => $this->type->value,
        ];
    }
}