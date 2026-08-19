<?php

namespace App\Domain\Report;

use App\Domain\Entity;

class ReportEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $productId,
        public readonly int $userId,
        public string $title,
        public string $description,
        public bool $wasSucessful,
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
            'product_id' => $this->productId,
            'user_id' => $this->userId,
            'title' => $this->title,
            'description' => $this->description,
            'was_sucessful' => $this->wasSucessful,
            'is_active' => $this->isActive,
        ];
    }
}
