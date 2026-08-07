<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
/**
 * @property int $id
 * @property string $name
 * @property string $country
 * @property int $reportCount
 * @property Collection $reports
 * @property bool $is_active
 * @property Carbon $created_at
 * @property string $avatar_url
 */
class ScammerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'reports' => $this->reportCount,
            'avatar_url' => $this->avatar_url,
            'products' => $this->reports->pluck('product.name')->filter()->unique()->values()->all(),
            'status' => $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}