<?php
namespace App\Repositories\Organization;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Organization;

interface OrganizationRepositoryInterface {
    public function getActiveOrganizations(): Collection;
    public function getActiveScammers(Organization $organization): Collection;
}