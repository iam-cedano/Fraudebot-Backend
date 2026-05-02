<?php

namespace App\Domain\ScammerPaymentMethod\ValueObjects;

use function strlen;

class CardNumber {

    private string $number;

    public function __construct(string $number) {
        $cleanValue = preg_replace('/\D/', '', $number);

        if (strlen($cleanValue) !== 16) {
            throw new \InvalidArgumentException("Card number must be 16 digits long.");
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