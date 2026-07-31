<?php

namespace App\Http\Resources\Public;

use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

/**
 * @property int $id
 * @property string $name
 * @property string $country
 * @property bool $is_active
 * @property Collection $reports
 * @property Carbon $created_at
 * @property Carbon $updated_at
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
            'reports' => $this->reports->count(),
            'products' => $this->reports->pluck('product.name')->filter()->unique()->values()->all(),
            'type' => 'organization',
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}