<?php

namespace App\Http\Resources\Public;

use App\Models\Scammer;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/**
 * @mixin Scammer
 *
 * @property int $reports
 * @property array<int, string> $organizations
 * @property array<int, string> $products
 */
class ScammerCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'is_active' => $this->is_active,
            'reports' => $this->reports,
            'organizations' => $this->organizations,
            'products' => $this->products,
            'type' => 'scammer',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}