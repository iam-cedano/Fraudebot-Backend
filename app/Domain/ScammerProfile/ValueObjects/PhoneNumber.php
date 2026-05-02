<?php

namespace App\Domain\ScammerProfile\ValueObjects;

use function strlen;

class PhoneNumber {

    private string $phoneNumber;

    public function __construct(string $phoneNumber) {
        $cleanValue = preg_replace('/\D/', '', $phoneNumber);

        if (strlen($cleanValue) > 15) {
            throw new \InvalidArgumentException("Phone number must not exceed 15 digits long.");
        }

        $this->phoneNumber = $cleanValue;
    }

    public function getValue(): string {
        return $this->phoneNumber;
    }

    public function __tostring(): string {
        return $this->phoneNumber;
    }
}