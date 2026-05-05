<?php

namespace App\Models;

use App\Domain\Organization\OrganizationEntity;
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

    protected $casts = [
        'is_active' => 'boolean',
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
        return $this->hasMany(OrganizationAccessPoint::class);
    }

    /**
     * Get the reports associated with the organization.
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Get the payment methods associated with the organization.
     */
    public function paymentMethods()
    {
        return $this->hasMany(OrganizationPaymentMethod::class);
    }

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): OrganizationEntity
    {
        return new OrganizationEntity(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            isActive: $this->is_active,
        );
    }
}
