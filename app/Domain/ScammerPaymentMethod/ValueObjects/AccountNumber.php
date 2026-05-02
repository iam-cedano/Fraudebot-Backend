<?php

namespace App\Domain\ScammerPaymentMethod\ValueObjects;

use function strlen;

class AccountNumber {

    private string $number;

    public function __construct(string $number) {
        $cleanValue = preg_replace('/\D/', '', $number);

        if (strlen($cleanValue) !== 10) {
            throw new \InvalidArgumentException("Account number must be exactly 10 digits long.");
        }

        $this->number = $cleanValue;
    }

    public function getValue(): string
    {
        return $this->number;
    }

    public function __toString(): string
    {
        return $this->number;
    }
}