<?php

namespace App\Repositories\Organization;

use App\Models\Organization;
use Illuminate\Support\Collection;

interface OrganizationRepositoryInterface
{
    public function findOrganizationById(int $id): Organization|null;

    public function findCalendarByOrganizationIdAndYear(int $id, int $year): Collection|null;
}
