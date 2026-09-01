<?php

namespace App\Domain\Map\ValueObjects;

use App\Domain\Map\Enums\KindTypes;
use App\Domain\Map\Enums\NodeTypes;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

abstract class Node implements JsonSerializable
{
    abstract public function toArray(): array;

    abstract public static function from(Model $model): self;

    abstract public function graphId(): string;

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
