<?php

namespace App\Repositories\Search;

use App\Domain\Scammer\Enums\ClueType;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Repositories\Organization\OrganizationCardRepositoryInterface;
use App\Repositories\Scammer\ScammerCardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicSearchRepository implements SearchRepositoryInterface
{
    public function __construct(
        private ScammerCardRepositoryInterface $scammerCardRepository,
        private OrganizationCardRepositoryInterface $organizationCardRepository,
    ) {
    }

    public function find(Clue $clue, int $page, int $count): Collection
    {
        if ($clue->getType() === ClueType::Nothing) {
            return collect([]);
        }

        $clueType = strtolower($clue->getValue());

        $hashedKey = hash('sha256', "search:public:{$clueType}:$page:$count");

        return Cache::remember($hashedKey, 3600, function () use ($clue, $page, $count) {
            return $this->scammerCardRepository->find($clue, $page, $count / 2)
                ->merge($this->organizationCardRepository->find($clue, $page, $count / 2))
                ->sortByDesc('updated_at');
        });
    }
}
