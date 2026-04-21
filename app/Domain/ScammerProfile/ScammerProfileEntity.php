<?php

namespace App\Domain\ScammerProfile;

use App\Domain\Entity;
use App\Domain\ScammerProfile\Enums\SocialMediaType;

class ScammerProfileEntity extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public int $scammerId,
        public string $name,
        public SocialMediaType $socialMedia,
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
            'social_media' => $this->socialMedia,
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

        if (empty($this->socialMedia)) {
            throw new \InvalidArgumentException('Social media cannot be empty');
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
        $this->contact = strtolower(trim($this->contact));
    }
}
