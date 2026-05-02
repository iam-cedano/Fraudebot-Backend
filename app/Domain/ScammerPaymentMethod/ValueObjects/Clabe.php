<?php

namespace App\Domain\ScammerPaymentMethod\ValueObjects;

use function strlen;

class Clabe {

    private string $clabe;

    public function __construct(string $clabe) {
        $cleanValue = preg_replace('/\D/', '', $clabe);

        if (strlen($cleanValue) !== 18) {
            throw new \InvalidArgumentException("CLABE must be 18 digits long.");
        }

        $this->clabe = $cleanValue;
    }

    public function getClabe(): string
    {
        return $this->clabe;
    }

    public function __toString(): string
    {
        return $this->clabe;
    }

}