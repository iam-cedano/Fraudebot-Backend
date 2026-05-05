<?php

namespace App\Domain\PaymentMethod\ValueObjects;

class Reference {

    private string $reference;

    public function __construct(string $reference) {
        $this->reference = str_replace(' ', '', $reference);
    }

    public function getValue(): string
    {
        return $this->reference;
    }

    public function __toString(): string
    {
        return $this->reference;
    }
}