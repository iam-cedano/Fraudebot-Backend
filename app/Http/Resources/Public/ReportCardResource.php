<?php

namespace App\Http\Resources\Public;

use App\Models\Organization;
use App\Models\Scammer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return match (true) {
            $this->resource instanceof Scammer => (new ScammerCardResource($this->resource))->toArray($request),
            $this->resource instanceof Organization => (new OrganizationCardResource($this->resource))->toArray($request),
            default => [],
        };
    }
}
