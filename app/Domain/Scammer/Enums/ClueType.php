<?php

namespace App\Domain\Scammer\Enums;

enum ClueType
{
    case EMAIL;
    case PHONE;
    case URL;
    case CARD_NUMBER;
    case CLABE;
    case ACCOUNT_NUMBER;
    case NAME;
    case WALLET;
    case NOTHING;
}
