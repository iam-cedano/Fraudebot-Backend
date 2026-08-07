<?php

namespace App\Domain\Scammer;

use App\Domain\Entity;

class ScammerEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $country,
        public readonly string $avatarUrl,
        public readonly bool $isActive,
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
            'country' => $this->country,
            'avatar_url' => $this->avatarUrl,
            'is_active' => $this->isActive,
        ];
    }
}
