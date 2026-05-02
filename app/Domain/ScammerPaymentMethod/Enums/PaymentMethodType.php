<?php
namespace App\Domain\ScammerPaymentMethod\Enums;

enum PaymentMethodType: int
{
    case DEPOSIT = 2;
    case IBAN = 3;
    case CASH = 4;
    case CRYPTO = 5;
    case OTHER = 6;
}