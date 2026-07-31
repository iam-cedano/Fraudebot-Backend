<?php

namespace App\Repositories\Search;

use Illuminate\Support\Facades\Cache;

/**
 * Keys the public search cache by a version number so that any write to a
 * searchable model (scammers/organizations) can invalidate every cached
 * search result at once, without needing to know which keys exist.
 *
 * Stale entries are not actively deleted; they simply become unreachable
 * (the version bump changes the key) and expire naturally via their TTL.
 */
class SearchCache
{
    private const VERSION_KEY = 'search:public:cache_version';

    public static function key(string $suffix): string
    {
        return hash('sha256', sprintf('search:public:v%d:%s', self::version(), $suffix));
    }

    public static function invalidate(): void
    {
        if (Cache::get(self::VERSION_KEY) === null) {
            Cache::add(self::VERSION_KEY, 1);
        }

        Cache::increment(self::VERSION_KEY);
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
