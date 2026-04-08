<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScammerPaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scammer_id',
        'bank_number',
        'iso_country',
        'is_active',
    ];

    /**
     * Get the scammer that owns the payment method.
     */
    public function scammer()
    {
        return $this->belongsTo(Scammer::class);
    }
}
