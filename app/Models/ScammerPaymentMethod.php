<?php

namespace App\Models;

use App\Domain\ScammerPaymentMethod\ScammerPaymentMethodEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScammerPaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scammer_id',
        'reference',
        'is_active',
    ];

    /**
     * Override the create method to ensure that the reference is stored without spaces 
     */
    public static function create(array $attributes = [])
    {
        $attributes['reference'] = preg_replace('/\s+/', '', $attributes['reference']); 
    
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
            isActive: $this->is_active,
        );
    }
}
