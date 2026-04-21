<?php

namespace App\Models;

use App\Domain\Product\ProductEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'emoji',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the reports for the product.
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): ProductEntity
    {
        return new ProductEntity(
            id: $this->id,
            categoryId: $this->category_id,
            name: $this->name,
            emoji: $this->emoji,
        );
    }
}
