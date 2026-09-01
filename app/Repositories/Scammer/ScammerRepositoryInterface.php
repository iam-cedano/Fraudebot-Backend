<?php

namespace App\Repositories\Scammer;

use App\Domain\Map\ValueObjects\MapResult;
use App\Domain\Search\ValueObjects\PaginatedResult;
use App\Models\Scammer;
use Illuminate\Support\Collection;

interface ScammerRepositoryInterface
{
    public function findScammerById(int $id): ?Scammer;

    public function findCalendarByScammerIdAndYear(int $id, int $year): ?Collection;

    public function findContactsById(int $id): ?Collection;

    public function findPaginatedContactsById(int $id, int $page, int $count, ?string $platform = null): ?PaginatedResult;

    public function findPaginatedReportsById(int $id, int $page, int $count): ?PaginatedResult;

    public function findMapById(int $id): ?MapResult;
}
