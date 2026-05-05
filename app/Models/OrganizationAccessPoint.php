<?php

namespace App\Models;

use App\Domain\OrganizationAccessPoint\OrganizationAccessPointEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationAccessPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'PlatformType',
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
    public function toEntity(): OrganizationAccessPointEntity
    {
        return new OrganizationAccessPointEntity(
            id: $this->id,
            organizationId: $this->organization_id,
            PlatformType: $this->PlatformType,
            contact: $this->contact,
            isActive: $this->is_active,
        );
    }
}
