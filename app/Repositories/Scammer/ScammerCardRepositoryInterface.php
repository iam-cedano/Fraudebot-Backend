<?php
namespace App\Repositories\Scammer;

use App\Domain\Scammer\ValueObjects\Clue;
use Illuminate\Support\Collection;

interface ScammerCardRepositoryInterface {
    public function findAll(int $page, int $count, array $relationships = []): Collection;
    public function find(Clue $clue, int $page, int $count, array $relationships = []): Collection;
}