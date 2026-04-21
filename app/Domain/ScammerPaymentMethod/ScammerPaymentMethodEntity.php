<?php

namespace App\Domain\ScammerPaymentMethod;

use App\Domain\Entity;

class ScammerPaymentMethodEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $scammerId,
        public string $reference,
        public bool $isActive,
    ) {
        parent::__construct();
    }

    protected function transform(): void
    {
        // To be implemented
    }

    protected function validate(): void
    {
        // To be implemented
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'scammer_id' => $this->scammerId,
            'reference' => $this->reference,
            'is_active' => $this->isActive,
        ];
    }
}
