<?php

namespace App\Repositories\Scammer;

use App\Models\Scammer;
use Illuminate\Support\Collection;

interface ScammerRepositoryInterface
{
    public function findScammerById(int $id): Scammer|null;

    public function findCalendarByScammerIdAndYear(int $id, int $year): Collection|null;

    public function findContactsById(int $id): Collection|null;
    public function findPaginatedContactsById(int $id, int $page, int $count, string $platform = null): Collection|null;
}