<?php

namespace App\Models;

use App\Domain\Scammer\ScammerEntity;
use App\Repositories\Search\SearchCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $name
 * @property string $country
 * @property string $avatar_path
 * @property boolean $is_active
 */
class Scammer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'country',
        'avatar_path',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'reportCount',
    ];

    protected function reportCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reports()->count(),
        );
    }

    protected static function booted()
    {
        static::saved(function () {
            SearchCache::invalidate();
        });

        static::deleted(function (Scammer $scammer) {
            ScammerContact::query()->where('scammer_id', $scammer->id)->delete();
            ScammerPaymentMethod::query()->where('scammer_id', $scammer->id)->delete();

            SearchCache::invalidate();
        });

        static::restoring(function (Scammer $scammer) {
            ScammerContact::onlyTrashed()->where('scammer_id', $scammer->id)->restore();
            ScammerPaymentMethod::onlyTrashed()->where('scammer_id', $scammer->id)->restore();
        });

        static::restored(function () {
            SearchCache::invalidate();
        });
    }

    /**
     * Get the contacts associated with the scammer.
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'scammers_contacts')
            ->using(ScammerContact::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    /**
     * Get the payment methods associated with the scammer.
     */
    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'scammers_payment_methods')
            ->using(ScammerPaymentMethod::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    /**
     * Get the organizations associated with the scammer.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'scammers_organizations', 'scammer_id', 'organization_id');
    }

    /**
     * Get the reports associated with the scammer.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): ScammerEntity
    {
        return new ScammerEntity(
            id: $this->id,
            name: $this->name,
            country: $this->country,
            avatarPath: $this->avatar_path,
            isActive: $this->is_active,
        );
    }
}
