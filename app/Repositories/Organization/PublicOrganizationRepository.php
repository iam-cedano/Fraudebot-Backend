<?php

namespace App\Repositories\Organization;

use App\Models\Organization;
use App\Models\Report;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
}