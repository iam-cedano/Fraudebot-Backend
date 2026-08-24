<?php

namespace App\Domain\PaymentMethod\Enums;

use function is_int;
use function is_string;
use function ctype_digit;
use function strcasecmp;

enum PaymentMethodType: int
{
    case CARD_NUMBER = 1;
    case CLABE = 2;
    case ACCOUNT_NUMBER = 3;
    case WALLET = 4;
    case OTHER = 5;

    public static function tryFromInput(mixed $value): ?self
    {
        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return self::tryFrom((int) $value);
        }

        if (!is_string($value)) {
            return null;
        }

        foreach (self::cases() as $case) {
            if (strcasecmp($case->name, $value) === 0) {
                return $case;
            }
        }

        return null;
    }
}
