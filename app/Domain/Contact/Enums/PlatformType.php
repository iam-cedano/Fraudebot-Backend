<?php

namespace App\Domain\Contact\Enums;

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
    case URL = 9;
    case OTHER = 10;

    public static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->name, $name) === 0) {
                return $case;
            }
        }

        return null;
    }
}
