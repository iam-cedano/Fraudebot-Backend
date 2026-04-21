<?php

namespace App\Domain\Scammer;

use App\Domain\Entity;

class ScammerEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public string $name,
        public string $isoCountry,
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
            'name' => $this->name,
            'iso_country' => $this->isoCountry,
            'is_active' => $this->isActive,
        ];
    }
}
