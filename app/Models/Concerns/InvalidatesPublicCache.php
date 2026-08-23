<?php

namespace App\Models\Concerns;

use App\Repositories\Search\SearchCache;

trait InvalidatesPublicCache
{
    public static function bootInvalidatesPublicCache(): void
    {
        static::saved(fn () => SearchCache::invalidate());
        static::deleted(fn () => SearchCache::invalidate());
    }
}
