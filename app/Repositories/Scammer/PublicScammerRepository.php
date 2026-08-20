<?php

namespace App\Repositories\Scammer;

use App\Models\Report;
use App\Models\Scammer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class PublicScammerRepository implements ScammerRepositoryInterface
{
    private const int CACHE_TTL_SECONDS = 3600;
    private const string CACHE_KEY = 'public_scammer_id_';

    public function findScammerById(int $id): Scammer|null
    {
        return Cache::remember(self::CACHE_KEY . $id, self::CACHE_TTL_SECONDS, fn() => Scammer::with('reports.product')->find($id));
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
}