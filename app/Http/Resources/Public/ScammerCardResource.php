<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ScammerCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'iso_country' => $this->iso_country,
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