<?php

namespace App\Repositories\Organization;

use App\Domain\Contact\Enums\PlatformType;
use App\Models\Organization;
use App\Models\Report;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicOrganizationRepository implements OrganizationRepositoryInterface
{
    private const int CACHE_TTL_SECONDS = 3600;
    private const string CACHE_KEY = 'public_organization_id_';

    public function findOrganizationById(int $id): Organization|null
    {
        return Cache::remember(self::CACHE_KEY . $id, self::CACHE_TTL_SECONDS, fn() => Organization::with('reports.product')->find($id));
    }

    public function findCalendarByOrganizationIdAndYear(int $id, int $year): Collection|null
    {
        return Cache::remember(self::CACHE_KEY . $id . '_calendar_' . $year, self::CACHE_TTL_SECONDS, function () use ($id, $year) {
            $organization = Organization::with('reports')->find($id);

            if (!$organization) {
                return null;
            }

            $monthsWithReports = $organization->reports->filter(fn(Report $report) => $report->created_at->year == $year)
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
            $organization = Organization::with('contacts')->find($id);

            if (!$organization) {
                return null;
            }

            return $organization->contacts;
        });
    }

    public function findPaginatedContactsById(int $id, int $page, int $count, string $platform = null): Collection|null
    {
        $cacheKey = self::CACHE_KEY . $id . '_contacts_paginated_' . $page . '_' . $count;

        if ($platform) {
            $cacheKey .= "_platform_{$platform}";
        }

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($id, $page, $count, $platform) {
            $organization = Organization::find($id);

            if (!$organization) {
                return null;
            }

            $query = $organization->contacts();

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