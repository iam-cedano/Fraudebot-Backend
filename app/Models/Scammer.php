<?php

namespace App\Models;

use App\Domain\Scammer\ScammerEntity;
use App\Repositories\Search\SearchCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $name
 * @property string $country
 * @property string $avatar_url
 * @property boolean $is_active
 */
class Scammer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'country',
        'avatar_url',
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

        static::deleted(function ($scammer) {
            $scammer->contacts()->delete();
            $scammer->paymentMethods()->delete();

            SearchCache::invalidate();
        });

        static::restoring(function ($scammer) {
            $scammer->contacts()->withTrashed()->restore();
            $scammer->paymentMethods()->withTrashed()->restore();
        });

        static::restored(function () {
            SearchCache::invalidate();
        });
    }

    /**
     * Get the contacts associated with the scammer.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the payment methods associated with the scammer.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the organizations associated with the scammer.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'scammers_organizations', 'scammer_id', 'organization_id');
    }

    /**
     * Get the reports associated with the scammer.
     */
    public function reports()
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
            avatarUrl: $this->avatar_url,
            isActive: $this->is_active,
        );
    }
}
