<?php

namespace App\Models;

use App\Domain\OrganizationPaymentMethod\OrganizationPaymentMethodEntity;
use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationPaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'reference',
        'payment_type',
        'is_active'
    ];

    protected $casts = [
        'payment_type' => PaymentMethodType::class,
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'payment_type_name',
    ];

    protected function paymentTypeName(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst(strtolower($this->payment_type->name)),
        );
    }

    public function toEntity(): OrganizationPaymentMethodEntity
    {
        return new OrganizationPaymentMethodEntity(
            id: $this->id,
            organizationId: $this->organization_id,
            reference: $this->reference,
            paymentType: $this->payment_type,
            isActive: $this->is_active,
        );
    }
}
