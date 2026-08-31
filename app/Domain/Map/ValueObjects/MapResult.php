<?php

namespace App\Domain\Map\ValueObjects;

use Illuminate\Support\Collection;

final class MapResult
{
    public function __construct(
        public readonly Collection $nodes,
        public readonly Collection $edges
    ) {
    }

    public static function empty(): self
    {
        return new self(collect(), collect());
    }

    public function isEmpty(): bool
    {
        return $this->nodes->isEmpty() && $this->edges->isEmpty();
    }

    public function toJson(): string {
        return json_encode([
            'nodes' => $this->nodes,
            'edges' => $this->edges,
        ]);
    }
}