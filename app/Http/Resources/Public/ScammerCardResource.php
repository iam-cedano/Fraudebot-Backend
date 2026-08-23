<?php

namespace App\Http\Resources\Public;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $country
 * @property bool $is_active
 * @property Collection $reports
 * @property Collection $organizations
 * @property Carbon $created_at
 * @property Carbon $updated_at
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
            'reports' => $this->reports->count(),
            'organizations' => $this->organizations->pluck('name')->all(),
            'products' => $this->reports->flatMap(fn ($report) => $report->products->pluck('name'))->filter()->unique()->values()->all(),
            'type' => 'scammer',
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}
