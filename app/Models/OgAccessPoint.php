<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OgAccessPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'platform',
        'contact',
        'is_active',
    ];

    /**
     * Get the organization that owns the access point.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
