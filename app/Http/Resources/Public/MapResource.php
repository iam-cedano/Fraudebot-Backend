<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property string $centerNode
 * @property Collection $nodes
 * @property Collection $edges
 */
class MapResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'centerNode' => $this->centerNode,
            'nodes' => NodeResource::collection($this->nodes),
            'edges' => EdgeResource::collection($this->edges),
        ];
    }
}