<?php

namespace App\Repositories\Scammer;

use App\Models\Scammer;
use Illuminate\Support\Collection;

interface ScammerRepositoryInterface
{
    public function findScammerById(int $id): Scammer|null;

    public function findCalendarByScammerIdAndYear(int $id, int $year): Collection|null;
}