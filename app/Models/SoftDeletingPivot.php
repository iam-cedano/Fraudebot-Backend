<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class SoftDeletingPivot extends Pivot
{
    use SoftDeletes;

    public $incrementing = true;

    public $timestamps = true;
}
