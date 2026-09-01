<?php

namespace App\Domain\Map\Enums;

enum EdgeKinds: string
{
    case CONTACT = 'contact';
    case PAYMENT = 'payment';
    case LINKED = 'linked';
}
