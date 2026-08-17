<?php

namespace App\Http\Resources\Public;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string $country
 * @property int $report_count
 * @property Collection $reports
 * @property bool $is_active
 * @property Carbon $created_at
 * @property string $avatar_path
 */
class OrganizationResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => config('countries')[$this->country] ?? 'Unknown',
            'reports' => $this->report_count,
            'avatar_path' => $this->avatar_path === null ? null : $publicDisk->url($this->avatar_path),
            'products' => $this->reports->pluck('product.name')->filter()->unique()->values()->all(),
            'status' => $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}