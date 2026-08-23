<?php

namespace App\Domain;

abstract class Entity
{
    public function __construct()
    {
        $this->transform();
        $this->validate();
    }

    abstract protected function transform(): void;

    abstract protected function validate(): void;

    abstract public function toArray(): array;
}
