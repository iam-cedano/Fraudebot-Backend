<?php

namespace App\Domain\Search;

final class CardPreview
{
    public const LIMIT = 5;

    /**
     * One extra row so callers can tell whether the list was truncated.
     */
    public const FETCH_LIMIT = self::LIMIT + 1;

    /**
     * @param  iterable<mixed>  $values
     * @return list<string>
     */
    public static function names(iterable $values, bool $unique = false): array
    {
        $items = [];

        foreach ($values as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            $items[] = $value;
        }

        if ($unique) {
            $items = array_values(array_unique($items));
        }

        sort($items, SORT_STRING);

        $preview = array_slice($items, 0, self::LIMIT);

        if (count($items) > self::LIMIT) {
            $preview[] = '...';
        }

        return $preview;
    }
}
