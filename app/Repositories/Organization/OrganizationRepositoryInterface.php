<?php

namespace App\Repositories\Organization;

use App\Models\Organization;
use Illuminate\Support\Collection;

interface OrganizationRepositoryInterface
{
    public function findOrganizationById(int $id): Organization|null;

    public function findCalendarByOrganizationIdAndYear(int $id, int $year): Collection|null;

    public function findContactsById(int $id): Collection|null;

    public function findPaginatedContactsById(int $id, int $page, int $count, string $platform = null): Collection|null;
}
