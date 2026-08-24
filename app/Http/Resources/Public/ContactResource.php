<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $reference
 * @property string $phone
 * @property string $platform_name
 * @property Carbon $created_at
 * @property bool $is_active
 */
class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reference' => $this->reference,
            'platform' => $this->platform_name,
            'created_at' => $this->created_at->format('d-m-Y'),
            'is_active' => $this->is_active,
        ];
    }
}
