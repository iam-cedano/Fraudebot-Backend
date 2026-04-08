<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the scammers associated with the organization.
     */
    public function scammers()
    {
        return $this->belongsToMany(Scammer::class, 'scammers_organizations');
    }

    /**
     * Get the access points associated with the organization.
     */
    public function accessPoints()
    {
        return $this->hasMany(OgAccessPoint::class);
    }

    /**
     * Get the reports associated with the organization.
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
