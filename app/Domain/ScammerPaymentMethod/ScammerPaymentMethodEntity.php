<?php

namespace App\Domain\ScammerPaymentMethod;

use App\Domain\Entity;
use App\Domain\ScammerPaymentMethod\Enums\PaymentMethodType;
use \InvalidArgumentException;

use function is_bool;
use function strlen;

class ScammerPaymentMethodEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $scammerId,
        public string $reference,
        public PaymentMethodType $paymentType,
        public bool $isActive,
    ) {
        parent::__construct();
    }

    protected function transform(): void
    {
        $this->reference = str_replace(' ', '', $this->reference);
    }

    protected function validate(): void
    {
        if (empty($this->reference) || $this->reference == '') {
            throw new InvalidArgumentException('Reference cannot be empty');
        }

        if (empty($this->paymentType)) {
            throw new InvalidArgumentException('Payment type cannot be empty');
        }

        if (empty($this->scammerId)) {
            throw new InvalidArgumentException('Scammer ID cannot be empty');
        }

        if (strlen($this->reference) > 255) {
            throw new InvalidArgumentException('Reference cannot exceed 255 characters');
        }

        if (!is_bool($this->isActive)) {
            throw new InvalidArgumentException('Is active must be a boolean value');
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'scammer_id' => $this->scammerId,
            'reference' => $this->reference,
            'payment_type' => $this->paymentType->value,
            'is_active' => $this->isActive,
        ];
    }
}
