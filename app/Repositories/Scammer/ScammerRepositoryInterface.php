<?php

namespace App\Repositories\Scammer;

use App\Models\Scammer;

interface ScammerRepositoryInterface {
    public function findScammerById(int $id): Scammer | null;
}