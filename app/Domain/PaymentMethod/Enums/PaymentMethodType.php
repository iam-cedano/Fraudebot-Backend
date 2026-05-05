<?php
namespace App\Domain\PaymentMethod\Enums;

enum PaymentMethodType: int
{
    case DEPOSIT = 1;
    case IBAN = 2;
    case CASH = 3;
    case CRYPTO = 4;
    case OTHER = 5;
}