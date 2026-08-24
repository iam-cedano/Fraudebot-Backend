<?php

namespace App\Domain\Scammer\ValueObjects;

use App\Domain\Scammer\Enums\ClueType;

class Clue
{
    private readonly ?string $value;

    public function __construct(?string $value)
    {
        $this->value = $value === null ? null : trim($value);
    }

    public function getValue(): string
    {
        if ($this->getType() === ClueType::PHONE) {
            return preg_replace('/\D+/', '', $this->value) ?? '';
        }

        return $this->value ?? '';
    }

    public function getType(): ClueType
    {

        if ($this->value === '' || $this->value === null) {
            return ClueType::NOTHING;
        }

        if (filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            return ClueType::EMAIL;
        }

        if (filter_var($this->value, FILTER_VALIDATE_IP)) {
            return ClueType::NOTHING;
        }

        if (filter_var($this->value, FILTER_VALIDATE_URL) || preg_match('/^(?!:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/i', $this->value) || preg_match('/^(http|https):\/\/[^ "]+$/i', $this->value)) {
            return ClueType::URL;
        }

        if (preg_match('/^\d{18}$/', $this->value)) {
            return ClueType::CLABE;
        }

        if (preg_match('/^\d{10}$/', $this->value)) {
            return ClueType::ACCOUNT_NUMBER;
        }

        if (preg_match('/^\d{16}$/', $this->value)) {
            return ClueType::CARD_NUMBER;
        }

        $strippedPhone = preg_replace('/[^0-9]/', '', $this->value);
        if (preg_match('/^(\+?52)?\d{10}$/', $strippedPhone) || (preg_match('/^\+?[0-9\-\(\)\s\.]+$/', $this->value) && strlen($strippedPhone) >= 7)) {
            return ClueType::PHONE;
        }

        if ($this->isCryptoWallet($this->value)) {
            return ClueType::WALLET;
        }

        return mb_strlen($this->value) >= 2 ? ClueType::NAME : ClueType::NOTHING;
    }

    private function isCryptoWallet(string $value): bool
    {
        return (bool) preg_match(
            '/^(?:0x[a-fA-F0-9]{40}|[13][a-km-zA-HJ-NP-Z1-9]{25,34}|(?:bc1|tb1|BC1|TB1)[a-zA-Z0-9]{25,87}|T[1-9A-HJ-NP-Za-km-z]{33})$/',
            $value,
        );
    }
}
