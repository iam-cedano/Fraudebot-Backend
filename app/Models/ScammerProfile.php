<?php

namespace App\Models;

use App\Domain\ScammerProfile\Enums\PlatformType;
use App\Domain\ScammerProfile\ScammerProfileEntity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScammerProfile extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'scammer_id',
        'name',
        'platform',
        'contact',
        'is_active',
    ];

    protected $appends = [
        'platform_name',
    ];

    protected $casts = [
        'id' => 'integer',
        'scammer_id' => 'integer',
        'contact' => 'string',
        'name' => 'string',
        'platform' => PlatformType::class,
        'is_active' => 'boolean',
    ];

    protected function platformName(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst(strtolower($this->platform->name)),
        );
    }

    /**
     * Get the scammer that owns the profile.
     */
    public function scammer()
    {
        return $this->belongsTo(Scammer::class);
    }

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): ScammerProfileEntity
    {
        return new ScammerProfileEntity(
            id: $this->id,
            scammerId: $this->scammer_id,
            name: $this->name,
            platformType: $this->platform,
            contact: $this->contact,
            isActive: $this->is_active,
        );
    }
}
