<?php

namespace App\Models;

use App\Domain\Report\ReportEntity;
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

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): ReportEntity
    {
        return new ReportEntity(
            id: $this->id,
            productId: $this->product_id,
            userId: $this->user_id,
            organizationId: $this->organization_id,
            title: $this->title,
            description: $this->description,
            wasSuccessful: $this->was_sucessful,
            isActive: $this->is_active,
        );
    }
}
