<?php

namespace App\Http\Resources\Public;

use App\Models\Organization;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/**
 * @mixin Organization
 *
 * @property string $country
 * @property int $reports
 * @property array<int, string> $products
 */
class OrganizationCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'is_active' => $this->is_active,
            'reports' => $this->reports,
            'products' => $this->products,
            'type' => 'organization',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}