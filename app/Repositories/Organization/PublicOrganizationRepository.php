<?php

namespace App\Repositories\Organization;

use App\Models\Organization;
use Illuminate\Support\Facades\Cache;

class PublicOrganizationRepository implements OrganizationRepositoryInterface
{

    private const int CACHE_TTL_SECONDS = 3600;
    private const string CACHE_KEY = 'public_organization_id_';

    public function findOrganizationById(int $id): Organization|null
    {
        return Cache::remember(self::CACHE_KEY . $id, self::CACHE_TTL_SECONDS, fn() => Organization::with('reports.product')->find($id));
    }
}