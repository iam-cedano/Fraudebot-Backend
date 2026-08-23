<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPublicCache;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class SoftDeletingPivot extends Pivot
{
    use InvalidatesPublicCache, SoftDeletes;

    public $incrementing = true;

    public $timestamps = true;
}
