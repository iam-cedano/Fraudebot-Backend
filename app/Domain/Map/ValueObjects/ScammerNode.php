<?php

namespace App\Domain\Map\ValueObjects;

use App\Domain\Map\Enums\NodeTypes;
use App\Domain\Map\Enums\KindTypes;
use App\Models\Scammer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ScammerNode extends Node {
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
        if (!$model instanceof Scammer) {
            throw new \InvalidArgumentException('Model must be an instance of Scammer');
        }

        return new self(
            $model->id,
            NodeTypes::PARTY,
            $model->id,
            $model->name,
            KindTypes::SCAMMER,
        );
    }

    /**
     * @param  Collection<int, Scammer>  $scammers
     * @return Collection<int, ScammerNode>
     */
    public static function fromCollection(Collection $scammers): Collection
    {
        return $scammers->map(fn (Scammer $scammer) => self::from($scammer));
    }

    public function toJson(): string {
        return json_encode([
            'id' => $this->id,
            'type' => $this->type->value,
            'party_id' => $this->partyId,
            'name' => $this->name,
            'kind' => $this->kind->value,
            'is_center' => $this->isCenter
        ]);
    }
}
