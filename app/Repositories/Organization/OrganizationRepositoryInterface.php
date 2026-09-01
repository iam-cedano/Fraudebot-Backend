<?php

namespace App\Repositories\Organization;

use App\Domain\Map\ValueObjects\MapResult;
use App\Domain\Search\ValueObjects\PaginatedResult;
use App\Models\Organization;
use Illuminate\Support\Collection;

interface OrganizationRepositoryInterface
{
    public function findOrganizationById(int $id): ?Organization;

    public function findCalendarByOrganizationIdAndYear(int $id, int $year): ?Collection;

    public function findContactsById(int $id): ?Collection;

    public function findPaginatedContactsById(int $id, int $page, int $count, ?string $platform = null): ?PaginatedResult;

    public function findPaginatedReportsById(int $id, int $page, int $count): ?PaginatedResult;

    public function findMapById(int $id): ?MapResult;
}
