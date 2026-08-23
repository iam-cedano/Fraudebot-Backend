<?php

namespace App\Repositories\Scammer;

use App\Domain\Contact\Enums\PlatformType;
use App\Models\Report;
use App\Models\Scammer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicScammerRepository implements ScammerRepositoryInterface
{
    private const int CACHE_TTL_SECONDS = 3600;
    private const string CACHE_KEY = 'public_scammer_id_';

    public function findScammerById(int $id): Scammer|null
    {
        return Cache::remember(self::CACHE_KEY . $id, self::CACHE_TTL_SECONDS, fn() => Scammer::with('reports.products')->find($id));
    }

    public function findCalendarByScammerIdAndYear(int $id, int $year): Collection|null
    {
        return Cache::remember(self::CACHE_KEY . $id . '_calendar_' . $year, self::CACHE_TTL_SECONDS, function () use ($id, $year) {
            $scammer = Scammer::with('reports')->find($id);

            if (!$scammer) {
                return null;
            }

            $monthsWithReports = $scammer->reports->filter(fn(Report $report) => $report->created_at->year == $year)
                ->groupBy(fn(Report $report) => $report->created_at->format('n'))
                ->map(fn(Collection $reports) => $reports->count());

            $months = collect(range(1, 12))
                ->mapWithKeys(fn(int $month) => [$month => $monthsWithReports->get($month, 0)]);

            return $months;
        });
    }

    public function findContactsById(int $id): Collection|null
    {
        return Cache::remember(self::CACHE_KEY . $id . '_contacts', self::CACHE_TTL_SECONDS, function () use ($id) {
            $scammer = Scammer::with('contacts')->find($id);

            if (!$scammer) {
                return null;
            }

            return $scammer->contacts;
        });
    }

    public function findPaginatedContactsById(int $id, int $page, int $count, string $platform = null): Collection|null
    {
        $cacheKey = self::CACHE_KEY . $id . '_contacts_paginated_' . $page . '_' . $count;

        if ($platform) {
            $cacheKey .= "_platform_{$platform}";
        }

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($id, $page, $count, $platform) {
            $scammer = Scammer::find($id);

            if (!$scammer) {
                return null;
            }

            $query = $scammer->contacts();

            if ($platform) {
                $platformType = PlatformType::tryFromName(Str::upper($platform));

                if (!$platformType) {
                    return collect();
                }

                $query->where('platform', $platformType);
            }

            return $query
                ->forPage($page, $count)
                ->get();
        });
    }
}