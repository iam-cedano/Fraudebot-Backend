<?php

namespace App\Models;

use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    public const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'organization_id',
        'scammer_id',
        'payment_type',
        'reference',
        'is_active',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scammer()
    {
        return $this->belongsTo(Scammer::class);
    }
}
