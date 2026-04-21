<?php
namespace App\Repositories\Organization;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Organization;

class PublicOrganizationRepository implements OrganizationRepositoryInterface {
    public function getActiveOrganizations(): Collection {
        return Organization::where('is_active', true)->get();
    }

    public function getActiveScammers(Organization $organization): Collection {
        return $organization->scammers()
            ->where('is_active', true)
            ->orderBy('scammers.id')
            ->get();
    }
}