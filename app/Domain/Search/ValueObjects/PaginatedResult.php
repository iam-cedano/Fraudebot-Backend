<?php

namespace App\Domain\Search\ValueObjects;

use Illuminate\Support\Collection;

final class PaginatedResult
{
    public function __construct(
        public readonly Collection $items,
        public readonly int $total,
    ) {}

    public static function empty(): self
    {
        return new self(collect(), 0);
    }
}
