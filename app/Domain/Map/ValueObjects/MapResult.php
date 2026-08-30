<?php

namespace App\Domain\Map\ValueObjects;

use Illuminate\Support\Collection;

final class MapResult
{
    public function __construct(
        public readonly string $centerNode,
        public readonly Collection $nodes,
        public readonly Collection $edges
    ) {
    }

    public static function empty(): self
    {
        return new self('', collect(), collect());
    }
}