<?php

namespace App\Domain\ScammerProfile\ValueObjects;

class URL {
    private string $url;

    public function __construct(string $url) {
        $cleanValue = preg_replace('/\D/', '', $url);

        if (!filter_var($cleanValue, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL format.");
        }

        $this->url = $cleanValue;
    }

    public function getValue(): string {
        return $this->url;
    }

    public function __toString(): string {
        return $this->url;
    }
}