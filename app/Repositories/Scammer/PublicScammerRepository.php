<?php

namespace App\Repositories\Scammer;

use App\Domain\Contact\Enums\PlatformType;
use App\Domain\Map\ValueObjects\MapResult;
use App\Domain\Search\ValueObjects\PaginatedResult;
use App\Models\Scammer;
use App\Repositories\Search\SearchCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicScammerRepository implements ScammerRepositoryInterface
{
    private const int CACHE_TTL_SECONDS = 3600;

    public function findScammerById(int $id): ?Scammer
    {
        return Cache::remember(
            SearchCache::key("public:scammer:{$id}"),
            self::CACHE_TTL_SECONDS,
            fn() => Scammer::query()
                ->where('is_active', true)
                ->with(['reports' => fn($query) => $query->where('is_active', true)->with('products')])
                ->withCount(['reports' => fn($query) => $query->where('is_active', true)])
                ->find($id),
        );
    }

    public function findCalendarByScammerIdAndYear(int $id, int $year): ?Collection
    {
        return Cache::remember(SearchCache::key("public:scammer:{$id}:calendar:{$year}"), self::CACHE_TTL_SECONDS, function () use ($id, $year) {
            $scammer = Scammer::query()->where('is_active', true)->with(['reports' => fn($query) => $query->where('is_active', true)])->find($id);

            if (!$scammer) {
                return null;
            }

            $monthsWithReports = $scammer->reports->filter(fn($report) => $report->created_at->year == $year)
                ->groupBy(fn($report) => $report->created_at->format('n'))
                ->map(fn(Collection $reports) => $reports->count());

            $months = collect(range(1, 12))
                ->mapWithKeys(fn(int $month) => [$month => $monthsWithReports->get($month, 0)]);

            return $months;
        });
    }

    public function findContactsById(int $id): ?Collection
    {
        return Cache::remember(SearchCache::key("public:scammer:{$id}:contacts"), self::CACHE_TTL_SECONDS, function () use ($id) {
            $scammer = Scammer::query()->where('is_active', true)->with(['contacts' => fn($query) => $query->where('is_active', true)])->find($id);

            if (!$scammer) {
                return null;
            }

            return $scammer->contacts;
        });
    }

    public function findPaginatedContactsById(int $id, int $page, int $count, ?string $platform = null): ?PaginatedResult
    {
        $cacheKey = "public:scammer:{$id}:contacts:{$page}:{$count}";

        if ($platform) {
            $cacheKey .= "_platform_{$platform}";
        }

        return Cache::remember(SearchCache::key($cacheKey), self::CACHE_TTL_SECONDS, function () use ($id, $page, $count, $platform) {
            $scammer = Scammer::query()->where('is_active', true)->find($id);

            if (!$scammer) {
                return null;
            }

            $query = $scammer->contacts()->where('is_active', true);

            if ($platform) {
                $platformType = PlatformType::tryFromName(Str::upper($platform));

                if (!$platformType) {
                    return PaginatedResult::empty();
                }

                $query->where('platform', $platformType);
            }

            $total = (clone $query)->count();
            $items = $query->forPage($page, $count)->get();

            return new PaginatedResult($items, $total);
        });
    }

    public function findMapById(int $id): ?MapResult
    {
        return Cache::remember(SearchCache::key("public:scammer:{$id}:map"), self::CACHE_TTL_SECONDS, function () use ($id) {
            $scammer = Scammer::query()->where('is_active', true)->find($id);

            if (!$scammer) {
                return MapResult::empty();
            }

            $organizations = $scammer->organizations()->where('is_active', true)->get();

            return new MapResult('', collect(), collect());
        });
    }
}
