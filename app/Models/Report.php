<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'organization_id',
        'title',
        'description',
        'was_sucessful',
        'is_active',
    ];

    /**
     * Get the product that owns the report.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that owns the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the report.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
