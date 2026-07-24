<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasicScammerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'iso_country' => $this->iso_country,
            'is_active' => $this->is_active,
            'reports' => $this->reports,
            'organizations' => BasicOrganizationResource::collection($this->whenLoaded('organizations')),
        ];
    }
}