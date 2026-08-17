<?php
namespace App\Domain\PaymentMethod\Enums;

enum PaymentMethodType: int
{
    case CARD_NUMBER = 1;
    case CLABE = 2;
    case ACCOUNT_NUMBER = 3;
    case WALLET = 4;
    case OTHER = 5;
}