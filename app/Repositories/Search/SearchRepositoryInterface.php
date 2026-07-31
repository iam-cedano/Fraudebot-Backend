<?php

namespace App\Repositories\Search;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Search\ValueObjects\CardSearchResult;

interface SearchRepositoryInterface
{
    public function find(Clue $clue, int $page, int $count): CardSearchResult;
}
