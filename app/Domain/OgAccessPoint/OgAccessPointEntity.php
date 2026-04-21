<?php

namespace App\Domain\OgAccessPoint;

use App\Domain\Entity;

class OgAccessPointEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $organizationId,
        public string $platform,
        public string $contact,
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
            'organization_id' => $this->organizationId,
            'platform' => $this->platform,
            'contact' => $this->contact,
            'is_active' => $this->isActive,
        ];
    }
}
