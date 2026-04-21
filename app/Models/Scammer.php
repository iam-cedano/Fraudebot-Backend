<?php

namespace App\Models;

use App\Domain\Scammer\ScammerEntity;
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

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($scammer) {
            $scammer->profiles()->delete();
            $scammer->paymentMethods()->delete();
        });

        static::restoring(function ($scammer) {
            $scammer->profiles()->withTrashed()->restore();
            $scammer->paymentMethods()->withTrashed()->restore();
        });
    }

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

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): ScammerEntity
    {
        return new ScammerEntity(
            id: $this->id,
            name: $this->name,
            isoCountry: $this->iso_country,
            isActive: $this->is_active,
        );
    }
}
