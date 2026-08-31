<?php

namespace App\Domain\Map\ValueObjects;

final class PaymentMethodNode {
    public function __construct(

    ) {}

    public function toJson(): string {
        return json_encode([]);
    }
}