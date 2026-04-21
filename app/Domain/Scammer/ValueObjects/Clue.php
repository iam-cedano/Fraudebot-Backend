<?php

namespace App\Domain\Scammer\ValueObjects;

use App\Domain\Scammer\Enums\ClueType;

class Clue
{
    public function __construct(private readonly string $value) {}

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): ClueType
    {
        if (filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            return ClueType::Email;
        }

        if (filter_var($this->value, FILTER_VALIDATE_IP)) {
            return ClueType::IpAddress;
        }

        // Check for URL or Domain Name
        if (filter_var($this->value, FILTER_VALIDATE_URL) || preg_match('/^(?!:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/i', $this->value) || preg_match('/^(http|https):\/\/[^ "]+$/i', $this->value)) {
            return ClueType::Url;
        }

        // Check for CLABE (18 digits)
        if (preg_match('/^\d{18}$/', $this->value)) {
            return ClueType::Clabe;
        }

        // Check for Account Number (10 digits)
        if (preg_match('/^\d{10}$/', $this->value)) {
            return ClueType::AccountNumber;
        }

        // Check for Credit Card (ensure is 16 digits)
        $strippedCard = str_replace([' ', '-'], '', $this->value);
        if (preg_match('/^\d{16}$/', $strippedCard)) {
            return ClueType::CardNumber;
        }

        // Check for phone number (Mexican targets: +52XXXXXXXXXX, 52XXXXXXXXXX, XXXXXXXXXX)
        $strippedPhone = preg_replace('/[^0-9]/', '', $this->value);
        if (preg_match('/^(\+?52)?\d{10}$/', $strippedPhone) || (preg_match('/^\+?[0-9\-\(\)\s\.]+$/', $this->value) && strlen($strippedPhone) >= 7)) {
            return ClueType::Phone;
        }

        return ClueType::General;
    }
}
