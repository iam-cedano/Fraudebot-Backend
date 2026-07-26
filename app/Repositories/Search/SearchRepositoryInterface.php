<?php

namespace App\Repositories\Search;

use App\Domain\Scammer\ValueObjects\Clue;
use Illuminate\Support\Collection;

interface SearchRepositoryInterface
{
    public function find(Clue $clue, int $page, int $count): Collection;
}