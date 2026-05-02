<?php

namespace App\Domain\ScammerProfile\ValueObjects;

class Email {
    private string $email;

    public function __construct(string $email) {
        $cleanValue = trim(strtolower($email));

        if (!filter_var($cleanValue, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format");
        }

        $this->email = $cleanValue;
    }

    public function getValue(): string {
        return $this->email;
    }

    public function __toString(): string {
        return $this->email;
    }
}