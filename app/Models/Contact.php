<?php

namespace App\Models;

use App\Domain\Contact\ContactEntity;
use App\Domain\Contact\Enums\PlatformType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
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
        'organization_id' => 'integer',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scammer()
    {
        return $this->belongsTo(Scammer::class);
    }

    public function toEntity(): ContactEntity
    {
        return new ContactEntity(
            id: $this->id,
            organizationId: $this->organization_id,
            scammerId: $this->scammer_id,
            name: $this->name,
            platformType: $this->platform,
            contact: $this->contact,
            isActive: $this->is_active,
        );
    }
}
