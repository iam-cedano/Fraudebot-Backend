<?php

namespace App\Repositories\Organization;

use App\Domain\Contact\Enums\PlatformType;
use App\Domain\Search\ValueObjects\PaginatedResult;
use App\Models\Organization;
use App\Repositories\Search\SearchCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicOrganizationRepository implements OrganizationRepositoryInterface
{
    private const int CACHE_TTL_SECONDS = 3600;

    public function findOrganizationById(int $id): ?Organization
    {
        return Cache::remember(
            SearchCache::key("public:organization:{$id}"),
            self::CACHE_TTL_SECONDS,
            fn () => Organization::query()
                ->where('is_active', true)
                ->with(['reports' => fn ($query) => $query->where('is_active', true)->with('products')])
                ->withCount(['reports' => fn ($query) => $query->where('is_active', true)])
                ->find($id),
        );
    }

    public function findCalendarByOrganizationIdAndYear(int $id, int $year): ?Collection
    {
        return Cache::remember(SearchCache::key("public:organization:{$id}:calendar:{$year}"), self::CACHE_TTL_SECONDS, function () use ($id, $year) {
            $organization = Organization::query()->where('is_active', true)->with(['reports' => fn ($query) => $query->where('is_active', true)])->find($id);

            if (! $organization) {
                return null;
            }

            $monthsWithReports = $organization->reports->filter(fn ($report) => $report->created_at->year == $year)
                ->groupBy(fn ($report) => $report->created_at->format('n'))
                ->map(fn (Collection $reports) => $reports->count());

            $months = collect(range(1, 12))
                ->mapWithKeys(fn (int $month) => [$month => $monthsWithReports->get($month, 0)]);

            return $months;
        });
    }

    public function findContactsById(int $id): ?Collection
    {
        return Cache::remember(SearchCache::key("public:organization:{$id}:contacts"), self::CACHE_TTL_SECONDS, function () use ($id) {
            $organization = Organization::query()->where('is_active', true)->with(['contacts' => fn ($query) => $query->where('is_active', true)])->find($id);

            if (! $organization) {
                return null;
            }

            return $organization->contacts;
        });
    }

    public function findPaginatedContactsById(int $id, int $page, int $count, ?string $platform = null): ?PaginatedResult
    {
        $cacheKey = "public:organization:{$id}:contacts:{$page}:{$count}";

        if ($platform) {
            $cacheKey .= "_platform_{$platform}";
        }

        return Cache::remember(SearchCache::key($cacheKey), self::CACHE_TTL_SECONDS, function () use ($id, $page, $count, $platform) {
            $organization = Organization::query()->where('is_active', true)->find($id);

            if (! $organization) {
                return null;
            }

            $query = $organization->contacts()->where('is_active', true);

            if ($platform) {
                $platformType = PlatformType::tryFromName(Str::upper($platform));

                if (! $platformType) {
                    return PaginatedResult::empty();
                }

                $query->where('platform', $platformType);
            }

            $total = (clone $query)->count();
            $items = $query->forPage($page, $count)->get();

            return new PaginatedResult($items, $total);
        });
    }
}
