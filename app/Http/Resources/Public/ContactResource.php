<?php

namespace App\Http\Resources\Public;

use App\Domain\Contact\Enums\PlatformType;
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
            'reference' => $this->maskedReference(),
            'platform' => $this->platform_name,
            'created_at' => $this->created_at->format('d-m-Y'),
            'is_active' => $this->is_active,
        ];
    }

    private function maskedReference(): string
    {
        if ($this->platform === PlatformType::EMAIL) {
            [$local, $domain] = array_pad(explode('@', $this->reference, 2), 2, '');

            return mb_substr($local, 0, 1).'***@'.$domain;
        }

        if (in_array($this->platform, [PlatformType::CELLPHONE, PlatformType::WHATSAPP], true)) {
            return '***'.mb_substr($this->reference, -4);
        }

        return $this->reference;
    }
}
