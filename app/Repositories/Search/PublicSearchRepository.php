<?php
namespace App\Repositories\Search;

use App\Domain\Scammer\ValueObjects\Clue;
use Illuminate\Support\Collection;

class PublicSearchRepository implements SearchRepositoryInterface
{
    public function find(Clue $clue, int $page, int $count): Collection
    {
        if ($clue->getValue() == null || trim($clue->getValue()) == '') {
            return collect([]);
        }

        return collect([
            (object) [
                'id' => 1,
                'name' => 'John Doe',
                'reports' => 13,
                'iso_country' => 'MX',
                'is_active' => true,
                'organizations' => ['Ecohuertas'],
                'products' => ['Invertions', 'Crypto', 'NFT'],
                'type' => 'scammer',
            ],
            (object) [
                'id' => 2,
                'name' => 'Ecohuertas',
                'reports' => 35,
                'iso_country' => 'MX',
                'is_active' => true,
                'organizations' => [],
                'products' => ['Crypto'],
                'type' => 'organization',
            ],
        ]);
    }
}
