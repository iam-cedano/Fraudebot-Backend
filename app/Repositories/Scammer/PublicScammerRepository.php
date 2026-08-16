<?php

namespace App\Repositories\Scammer;

use App\Models\Scammer;
use Illuminate\Support\Facades\Cache;

class PublicScammerRepository implements ScammerRepositoryInterface
{
    private const CACHE_TTL_SECONDS = 3600;

    public function findScammerById(int $id): Scammer|null
    {
        $cacheKey = "scammer_id_$id";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, fn() => Scammer::with('reports.product')->find($id));
    }

}