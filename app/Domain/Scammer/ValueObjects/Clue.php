<?php

namespace App\Domain\Scammer\ValueObjects;

use App\Domain\Scammer\Enums\ClueType;

class Clue
{
    public function __construct(private readonly string|null $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): ClueType
    {

        if ($this->value === '' || $this->value === null) {
            return ClueType::Nothing;
        }

        if (filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            return ClueType::Email;
        }

        if (filter_var($this->value, FILTER_VALIDATE_IP)) {
            return ClueType::IpAddress;
        }

        if (filter_var($this->value, FILTER_VALIDATE_URL) || preg_match('/^(?!:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/i', $this->value) || preg_match('/^(http|https):\/\/[^ "]+$/i', $this->value)) {
            return ClueType::Url;
        }

        if (preg_match('/^\d{18}$/', $this->value)) {
            return ClueType::Clabe;
        }

        if (preg_match('/^\d{10}$/', $this->value)) {
            return ClueType::AccountNumber;
        }

        if (preg_match('/^\d{16}$/', $this->value)) {
            return ClueType::CardNumber;
        }

        $strippedPhone = preg_replace('/[^0-9]/', '', $this->value);
        if (preg_match('/^(\+?52)?\d{10}$/', $strippedPhone) || (preg_match('/^\+?[0-9\-\(\)\s\.]+$/', $this->value) && strlen($strippedPhone) >= 7)) {
            return ClueType::Phone;
        }

        return ClueType::Name;
    }
}
