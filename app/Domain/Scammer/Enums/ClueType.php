<?php

namespace App\Domain\Scammer\Enums;

enum ClueType
{
    case Email;
    case Phone;
    case Url;
    case IpAddress;
    case CardNumber;
    case Clabe;
    case AccountNumber;
    case Username;
    case General;
}
