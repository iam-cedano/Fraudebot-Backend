<?php

namespace App\Http\Resources\Public\Concerns;

use App\Domain\Search\CardPreview;
use App\Models\Organization;
use App\Models\Report;
use App\Models\Scammer;

trait ResolvesCardPreviewLists
{
    /**
     * @return list<string>
     */
    protected function previewOrganizationNames(): array
    {
        /** @var Scammer|Organization $model */
        $model = $this->resource;
        $names = $model->getAttribute('card_organization_names');

        if (! is_array($names) && $model instanceof Scammer && $model->relationLoaded('organizations')) {
            $names = $model->organizations->pluck('name');
        }

        return CardPreview::names(is_iterable($names) ? $names : []);
    }

    /**
     * @return list<string>
     */
    protected function previewProductNames(): array
    {
        /** @var Scammer|Organization $model */
        $model = $this->resource;
        $names = $model->getAttribute('card_product_names');

        if (! is_array($names) && $model->relationLoaded('reports')) {
            $names = $model->reports->flatMap(function ($report) {
                if (! $report instanceof Report || ! $report->relationLoaded('products')) {
                    return [];
                }

                return $report->products->pluck('name');
            });
        }

        return CardPreview::names(is_iterable($names) ? $names : [], unique: true);
    }
}
