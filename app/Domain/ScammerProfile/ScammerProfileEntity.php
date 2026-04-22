<?php

namespace App\Domain\ScammerProfile;

use App\Domain\Entity;
use App\Domain\ScammerProfile\Enums\PlatformType;
use App\Domain\ScammerProfile\ValueObjects\Platform;

class ScammerProfileEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public int $scammerId,
        public string $name,
        public PlatformType $platformType,
        public string $contact,
        public bool $isActive,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'scammer_id' => $this->scammerId,
            'name' => $this->name,
            'platform' => $this->platformType,
            'contact' => $this->contact,
            'is_active' => $this->isActive,
        ];
    }

    protected function validate(): void
    {
        if (empty($this->name) || $this->name == '') {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        if (empty($this->contact) || $this->contact == '') {
            throw new \InvalidArgumentException('Contact cannot be empty');
        }

        if (empty($this->platformType)) {
            throw new \InvalidArgumentException('PlatformType type cannot be empty');
        }

        if (empty($this->scammerId)) {
            throw new \InvalidArgumentException('Scammer ID cannot be empty');
        }

        if (strlen($this->name) > 50) {
            throw new \InvalidArgumentException('Name cannot exceed 50 characters');
        }

        if (strlen($this->contact) > 100) {
            throw new \InvalidArgumentException('Contact cannot exceed 100 characters');
        }

        if (!is_bool($this->isActive)) {
            throw new \InvalidArgumentException('Is active must be a boolean');
        }
    }

    protected function transform(): void {
        $this->name = trim($this->name);
        # $this->contact = strtolower(trim($this->contact));

        if (filter_var($this->contact, FILTER_VALIDATE_URL) || preg_match('/^(?!:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/i', $this->contact) || preg_match('/^(http|https):\/\/[^ "]+$/i', $this->contact)) {
            $socialMediaVO = new Platform($this->platformType);

            $this->contact = $socialMediaVO->extractURL($this->contact);
        }
    }
}
