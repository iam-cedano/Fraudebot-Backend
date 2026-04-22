<?php

namespace App\Domain\ScammerProfile\Enums;

enum PlatformType: int
{
    case WHATSAPP = 1;
    case FACEBOOK = 2;
    case YOUTUBE = 3;
    case TIKTOK = 4;
    case EMAIL = 5;
    case CELLPHONE = 6;
    case TELEGRAM = 7;
    case INSTAGRAM = 8;
}
