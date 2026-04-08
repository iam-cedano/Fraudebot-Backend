<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scammer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'iso_country',
        'is_active',
    ];

    /**
     * Get the profiles associated with the scammer.
     */
    public function profiles()
    {
        return $this->hasMany(ScammerProfile::class);
    }

    /**
     * Get the payment methods associated with the scammer.
     */
    public function paymentMethods()
    {
        return $this->hasMany(ScammerPaymentMethod::class);
    }

    /**
     * Get the organizations associated with the scammer.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'scammers_organizations');
    }
}
