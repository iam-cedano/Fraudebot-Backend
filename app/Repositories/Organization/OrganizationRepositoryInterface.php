<?php
namespace App\Repositories\Organization;

use App\Models\Organization;

interface OrganizationRepositoryInterface
{
    public function findOrganizationById(int $id): Organization|null;
}
