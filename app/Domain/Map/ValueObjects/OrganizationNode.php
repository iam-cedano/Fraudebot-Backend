<?php

namespace App\Domain\Map\ValueObjects;

use App\Domain\Map\Enums\NodeTypes;
use App\Domain\Map\Enums\KindTypes;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class OrganizationNode extends Node {
    private function __construct(
        private readonly string $id,
        private readonly NodeTypes $type,
        private readonly string $partyId,
        private readonly string $name,
        private readonly KindTypes $kind,
        private readonly ?bool $isCenter = false
    ) {
        parent::__construct($id, $type);
    }

    public static function from(Model $model): self {
        if (!$model instanceof Organization) {
            throw new \InvalidArgumentException('Model must be an instance of Organization');
        }

        return new self(
            $model->id,
            NodeTypes::PARTY,
            $model->id,
            $model->name,
            KindTypes::ORGANIZATION,
        );
    }

    public static function fromCollection(Collection $organizations): Collection
    {
        return $organizations->map(fn (Organization $organization) => self::from($organization));
    }

    public function centered(): self {
        $this->isCenter = true;
        
        return $this;
    }

    public function toJson(): string {
        return json_encode([
            'id' => 1,
            'type' => $this->type->value,
            'party_id' => $this->partyId,
            'name' => $this->name,
            'kind' => $this->kind->value,
            'is_center' => $this->isCenter
        ]);
    }
}
