<?php

namespace App\Domain\Contact;

use App\Domain\Contact\Enums\PlatformType;
use App\Domain\Contact\ValueObjects\Platform;
use App\Domain\Entity;

class ContactEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public string $name,
        public PlatformType $platformType,
        public string $reference,
        public bool $isActive,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'platform' => $this->platformType,
            'reference' => $this->reference,
            'is_active' => $this->isActive,
        ];
    }

    protected function validate(): void
    {
        if (empty($this->name) || $this->name == '') {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        if (empty($this->reference) || $this->reference == '') {
            throw new \InvalidArgumentException('Reference cannot be empty');
        }

        if (empty($this->platformType)) {
            throw new \InvalidArgumentException('PlatformType type cannot be empty');
        }

        if (strlen($this->name) > 50) {
            throw new \InvalidArgumentException('Name cannot exceed 50 characters');
        }

        if (strlen($this->reference) > 255) {
            throw new \InvalidArgumentException('Reference cannot exceed 255 characters');
        }

        if (!is_bool($this->isActive)) {
            throw new \InvalidArgumentException('Is active must be a boolean');
        }
    }

    protected function transform(): void {
        $this->name = trim($this->name);

        if (filter_var($this->reference, FILTER_VALIDATE_URL) || preg_match('/^(?!:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/i', $this->reference) || preg_match('/^(http|https):\/\/[^ "]+$/i', $this->reference)) {
            $socialMediaVO = new Platform($this->platformType);

            $this->reference = $socialMediaVO->extractURL($this->reference);
        }
    }
}
