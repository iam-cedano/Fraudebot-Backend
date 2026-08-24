<?php

namespace App\Repositories\Search;

use App\Domain\Scammer\Enums\ClueType;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Search\ValueObjects\CardSearchResult;
use App\Repositories\Organization\OrganizationCardRepositoryInterface;
use App\Repositories\Scammer\ScammerCardRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicSearchRepository implements SearchRepositoryInterface
{
    private const CACHE_TTL_SECONDS = 3600;

    private const SOURCE_SCAMMER = 'scammer';

    private const SOURCE_ORGANIZATION = 'organization';

    public function __construct(
        private ScammerCardRepositoryInterface $scammerCardRepository,
        private OrganizationCardRepositoryInterface $organizationCardRepository,
    ) {}

    public function find(Clue $clue, int $page, int $count): CardSearchResult
    {
        if ($clue->getType() === ClueType::NOTHING) {
            return CardSearchResult::empty();
        }

        $clueValue = strtolower($clue->getValue());
        $cacheKey = SearchCache::key("{$clueValue}:{$page}:{$count}");

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            fn () => $this->search($clue, $page, $count),
        );
    }

    private function search(Clue $clue, int $page, int $count): CardSearchResult
    {
        $scammerQuery = $this->scammerCardRepository->matchQuery($clue);
        $organizationQuery = $this->organizationCardRepository->matchQuery($clue);

        $total = ($scammerQuery?->count() ?? 0) + ($organizationQuery?->count() ?? 0);

        if ($total === 0) {
            return CardSearchResult::empty();
        }

        $candidates = $this->rankedPage($scammerQuery, $organizationQuery, $page, $count);

        if ($candidates->isEmpty()) {
            return new CardSearchResult(collect(), $total);
        }

        $scammerModels = $this->scammerCardRepository->hydrate(
            $candidates->where('source_type', self::SOURCE_SCAMMER)->pluck('id')->all(),
        );

        $organizationModels = $this->organizationCardRepository->hydrate(
            $candidates->where('source_type', self::SOURCE_ORGANIZATION)->pluck('id')->all(),
        );

        $items = $candidates
            ->map(fn ($row) => $row->source_type === self::SOURCE_SCAMMER
                ? $scammerModels->get($row->id)
                : $organizationModels->get($row->id))
            ->filter()
            ->values();

        return new CardSearchResult($items, $total);
    }

    /**
     * Ranks matching rows from both tables together at the database level
     * (a UNION ALL of just `id`/`updated_at`, ordered and paginated in SQL)
     * so pagination stays exact regardless of how many rows either table
     * has, without needing to fetch and merge candidate sets in PHP.
     */
    private function rankedPage(?Builder $scammerQuery, ?Builder $organizationQuery, int $page, int $count): Collection
    {
        $subQueries = array_filter([
            $scammerQuery ? $this->asRankable(clone $scammerQuery, self::SOURCE_SCAMMER) : null,
            $organizationQuery ? $this->asRankable(clone $organizationQuery, self::SOURCE_ORGANIZATION) : null,
        ]);

        if ($subQueries === []) {
            return collect();
        }

        $union = array_shift($subQueries);
        foreach ($subQueries as $subQuery) {
            $union->unionAll($subQuery);
        }

        return DB::query()
            ->fromSub($union, 'search_candidates')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->offset(($page - 1) * $count)
            ->limit($count)
            ->get();
    }

    private function asRankable(Builder $query, string $sourceType): QueryBuilder
    {
        // toBase() must run before select(): it's what actually applies
        // Eloquent's global scopes (e.g. SoftDeletingScope) as real WHERE
        // clauses. select() on the Eloquent builder itself would silently
        // skip that and leak soft-deleted rows into the union.
        return $query
            ->toBase()
            ->select(['id', DB::raw("'{$sourceType}' as source_type"), 'updated_at']);
    }
}
