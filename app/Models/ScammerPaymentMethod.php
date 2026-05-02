<?php

namespace App\Models;

use App\Domain\ScammerPaymentMethod\Enums\PaymentMethodType;
use App\Domain\ScammerPaymentMethod\ScammerPaymentMethodEntity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScammerPaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scammer_id',
        'reference',
        'payment_type',
        'is_active',
    ];

    protected $appends = [
        'payment_type_name',
    ];

    protected $casts = [
        'payment_type' => PaymentMethodType::class,
        'is_active' => 'boolean',
    ];

    protected function paymentTypeName(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst(strtolower($this->payment_type->name)),
        );
    }

    /**
     * Override the create method to ensure that the reference is stored without spaces 
     */
    public static function create(array $attributes = [])
    {
        return parent::create($attributes);
    }

    /**
     * Get the scammer that owns the payment method.
     */
    public function scammer()
    {
        return $this->belongsTo(Scammer::class);
    }

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): ScammerPaymentMethodEntity
    {
        return new ScammerPaymentMethodEntity(
            id: $this->id,
            scammerId: $this->scammer_id,
            reference: $this->reference,
            paymentType: $this->payment_type,
            isActive: $this->is_active,
        );
    }
}
