<?php

namespace App\Repositories\Scammer;

use App\Models\Scammer;
use Illuminate\Support\Facades\Cache;

class PublicScammerRepository implements ScammerRepositoryInterface
{
    private const int CACHE_TTL_SECONDS = 3600;
    private const string CACHE_KEY = 'public_scammer_id_';

    public function findScammerById(int $id): Scammer|null
    {
        return Cache::remember(self::CACHE_KEY . $id, self::CACHE_TTL_SECONDS, fn() => Scammer::with('reports.product')->find($id));
    }
}