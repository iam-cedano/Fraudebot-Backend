<?php

namespace App\Models;

use App\Domain\OgAccessPoint\OgAccessPointEntity;
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

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): OgAccessPointEntity
    {
        return new OgAccessPointEntity(
            id: $this->id,
            organizationId: $this->organization_id,
            platform: $this->platform,
            contact: $this->contact,
            isActive: $this->is_active,
        );
    }
}
