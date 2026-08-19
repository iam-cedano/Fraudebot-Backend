<?php

namespace App\Models;

use App\Domain\Organization\OrganizationEntity;
use App\Repositories\Search\SearchCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'country',
        'avatar_path',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saved(function () {
            SearchCache::invalidate();
        });

        static::deleted(function (Organization $organization) {
            OrganizationContact::query()->where('organization_id', $organization->id)->delete();
            OrganizationPaymentMethod::query()->where('organization_id', $organization->id)->delete();
            OrganizationReport::query()->where('organization_id', $organization->id)->delete();

            SearchCache::invalidate();
        });

        static::restoring(function (Organization $organization) {
            OrganizationContact::onlyTrashed()->where('organization_id', $organization->id)->restore();
            OrganizationPaymentMethod::onlyTrashed()->where('organization_id', $organization->id)->restore();
            OrganizationReport::onlyTrashed()->where('organization_id', $organization->id)->restore();
        });

        static::restored(function () {
            SearchCache::invalidate();
        });
    }

    public function reportCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reports()->count(),
        );
    }

    /**
     * Get the scammers associated with the organization.
     */
    public function scammers(): BelongsToMany
    {
        return $this->belongsToMany(Scammer::class, 'scammers_organizations');
    }

    /**
     * Get the contacts associated with the organization.
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'organizations_contacts')
            ->using(OrganizationContact::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    /**
     * Get the reports associated with the organization.
     */
    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'organizations_reports')
            ->using(OrganizationReport::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    /**
     * Get the payment methods associated with the organization.
     */
    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'organizations_payment_methods')
            ->using(OrganizationPaymentMethod::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
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
            country: $this->country,
            isActive: $this->is_active,
        );
    }
}
