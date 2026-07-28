<?php

namespace App\Repositories\Organization;

use App\Domain\Scammer\ValueObjects\Clue;
use Illuminate\Support\Collection;

interface OrganizationCardRepositoryInterface
{
    public function find(Clue $clue, int $page, int $count): Collection;
}
