<?php

namespace App\Domain\Map\ValueObjects;

use App\Domain\Map\Enums\NodeTypes;
use Illuminate\Database\Eloquent\Model;

abstract class Node {
    public function __construct(
        private readonly string $id,
        private readonly NodeTypes $type,
    ) {}

    abstract public function toJson(): string;

    abstract public static function from(Model $model): self;
}