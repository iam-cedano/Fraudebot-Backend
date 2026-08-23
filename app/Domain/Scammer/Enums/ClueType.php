<?php

namespace App\Domain\Scammer\Enums;

enum ClueType
{
    case Email;
    case Phone;
    case Url;
    case CardNumber;
    case Clabe;
    case AccountNumber;
    case Name;
    case Nothing;
}
