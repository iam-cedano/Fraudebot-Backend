<?php

namespace App\Repositories\Search;

use App\Domain\Search\CardPreview;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CardPreviewLoader
{
    /**
     * @param  array<int, int>  $scammerIds
     * @return Collection<int, list<string>>
     */
    public static function organizationNamesByScammerId(array $scammerIds): Collection
    {
        if ($scammerIds === []) {
            return collect();
        }

        $ranked = DB::table('scammers_organizations as so')
            ->join('organizations as o', 'o.id', '=', 'so.organization_id')
            ->whereIn('so.scammer_id', $scammerIds)
            ->whereNull('o.deleted_at')
            ->select([
                'so.scammer_id as owner_id',
                'o.name',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY so.scammer_id ORDER BY o.name, o.id) as rn'),
            ]);

        return self::takePreviewNames($ranked);
    }

    /**
     * @param  array<int, int>  $scammerIds
     * @return Collection<int, list<string>>
     */
    public static function productNamesByScammerId(array $scammerIds): Collection
    {
        return self::productNames('scammers_reports', 'scammer_id', $scammerIds);
    }

    /**
     * @param  array<int, int>  $organizationIds
     * @return Collection<int, list<string>>
     */
    public static function productNamesByOrganizationId(array $organizationIds): Collection
    {
        return self::productNames('organizations_reports', 'organization_id', $organizationIds);
    }

    /**
     * @param  array<int, int>  $ownerIds
     * @return Collection<int, list<string>>
     */
    private static function productNames(string $pivotTable, string $ownerColumn, array $ownerIds): Collection
    {
        if ($ownerIds === []) {
            return collect();
        }

        $distinct = DB::table('products')
            ->join('reports_products', 'reports_products.product_id', '=', 'products.id')
            ->join('reports', 'reports.id', '=', 'reports_products.report_id')
            ->join($pivotTable, "{$pivotTable}.report_id", '=', 'reports.id')
            ->whereIn("{$pivotTable}.{$ownerColumn}", $ownerIds)
            ->where('reports.is_active', true)
            ->whereNull('reports.deleted_at')
            ->whereNull('reports_products.deleted_at')
            ->whereNull("{$pivotTable}.deleted_at")
            ->select([
                "{$pivotTable}.{$ownerColumn} as owner_id",
                'products.name',
            ])
            ->distinct();

        $ranked = DB::query()
            ->fromSub($distinct, 'distinct_names')
            ->select([
                'owner_id',
                'name',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY owner_id ORDER BY name) as rn'),
            ]);

        return self::takePreviewNames($ranked);
    }

    /**
     * @return Collection<int, list<string>>
     */
    private static function takePreviewNames(QueryBuilder $ranked): Collection
    {
        /** @var Collection<int, list<string>> $names */
        $names = DB::query()
            ->fromSub($ranked, 'ranked')
            ->where('rn', '<=', CardPreview::FETCH_LIMIT)
            ->orderBy('owner_id')
            ->orderBy('rn')
            ->get()
            ->groupBy(fn ($row) => (int) $row->owner_id)
            ->map(function ($rows): array {
                $preview = [];

                foreach ($rows as $row) {
                    if (is_string($row->name)) {
                        $preview[] = $row->name;
                    }
                }

                return $preview;
            });

        return $names;
    }
}
