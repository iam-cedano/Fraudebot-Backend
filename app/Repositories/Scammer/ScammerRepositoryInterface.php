<?php
namespace App\Repositories\Scammer;

use App\Domain\Scammer\ValueObjects\Clue;
use Illuminate\Support\Collection;

interface ScammerRepositoryInterface {
    public function findAll(int $page, int $count): Collection;
    public function find(Clue $clue, int $page, int $count): Collection;
}