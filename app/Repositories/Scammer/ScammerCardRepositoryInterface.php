<?php

namespace App\Repositories\Scammer;

use App\Domain\Scammer\ValueObjects\Clue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ScammerCardRepositoryInterface
{
    /**
     * The WHERE-clause-only query matching the clue, or null if this clue
     * type isn't searchable for scammers. Used for counting and for
     * building the cross-table ranking query, never fetched directly.
     */
    public function matchQuery(Clue $clue): ?Builder;

    /**
     * Hydrates the scammers for exactly these ids, keyed by id, with report
     * counts and truncated organization/product preview names.
     *
     * @param  array<int>  $ids
     */
    public function hydrate(array $ids): Collection;
}
